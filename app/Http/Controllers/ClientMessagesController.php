<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClientMessagesController extends Controller
{
    public function __construct(private FirebaseService $firebase)
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $threads = $this->threadsForClient((int) Auth::id());
        [$selectedEventId, $selectedUserId] = $this->selectedThreadIds($request, $threads);
        $selectedThread = collect($threads)->first(fn (array $thread) =>
            $thread['event_id'] === $selectedEventId && $thread['user_id'] === $selectedUserId
        );

        $messages = [];
        $lastMessageId = 0;
        if ($selectedThread) {
            $messages = $this->messagesForThread($selectedEventId, $selectedUserId);
            $lastMessageId = (float) (collect($messages)->last()['message_id'] ?? collect($messages)->last()['timestamp'] ?? 0);
            $this->firebase->markMessagesAsRead($selectedEventId, (int) Auth::id(), $selectedUserId, (int) Auth::id());
        }

        return view('userui.messages', compact(
            'threads',
            'selectedThread',
            'selectedEventId',
            'selectedUserId',
            'messages',
            'lastMessageId'
        ));
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'min:1'],
            'recipient_id' => ['required', 'integer', 'min:1'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $thread = collect($this->threadsForClient((int) Auth::id()))->first(fn (array $item) =>
            $item['event_id'] === (int) $data['event_id'] && $item['user_id'] === (int) $data['recipient_id']
        );
        abort_unless($thread, 403);

        $message = trim($data['message']);
        $saved = $this->firebase->saveMessage(
            (int) $data['event_id'],
            (int) Auth::id(),
            (int) $data['recipient_id'],
            $message,
            Auth::user()->full_name ?? 'Client'
        );

        if (!$saved) {
            return back()->withInput()->with('message_error', 'Message could not be sent.');
        }

        return redirect()->route('your.messages', [
            'event_id' => $data['event_id'],
            'user_id' => $data['recipient_id'],
        ]);
    }

    public function api(Request $request)
    {
        $eventId = (int) $request->integer('event_id');
        $otherUserId = (int) $request->integer('other_user_id');
        $thread = collect($this->threadsForClient((int) Auth::id()))->first(fn (array $item) =>
            $item['event_id'] === $eventId && $item['user_id'] === $otherUserId
        );
        abort_unless($thread, 403);

        if ($request->string('action')->toString() === 'mark_read') {
            $this->firebase->markMessagesAsRead($eventId, (int) Auth::id(), $otherUserId, (int) Auth::id());
            return response()->json(['success' => true]);
        }

        $lastId = (float) $request->input('last_id', 0);
        $messages = collect($this->firebase->getMessages($eventId, (int) Auth::id(), $otherUserId))
            ->filter(fn (array $message) => (float) ($message['message_id'] ?? $message['timestamp'] ?? 0) > $lastId)
            ->filter(fn (array $message) => trim((string) ($message['message'] ?? $message['body'] ?? '')) !== '')
            ->values();

        return response()->json(['messages' => $messages]);
    }

    private function selectedThreadIds(Request $request, array $threads): array
    {
        $eventId = (int) $request->integer('event_id');
        $userId = (int) $request->integer('user_id');

        if ($userId > 0 && $eventId === 0) {
            $thread = collect($threads)->firstWhere('user_id', $userId);
            $eventId = (int) ($thread['event_id'] ?? 0);
        }
        if ($eventId === 0 && $threads !== []) {
            $eventId = $threads[0]['event_id'];
            $userId = $threads[0]['user_id'];
        }
        if ($eventId > 0 && $userId === 0) {
            $thread = collect($threads)->firstWhere('event_id', $eventId);
            $userId = (int) ($thread['user_id'] ?? 0);
        }

        return [$eventId, $userId];
    }

    private function messagesForThread(int $eventId, int $otherUserId): array
    {
        return collect($this->firebase->getMessages($eventId, (int) Auth::id(), $otherUserId))
            ->filter(fn (array $message) => trim((string) ($message['message'] ?? $message['body'] ?? '')) !== '')
            ->map(function (array $message): array {
                if (empty($message['created_at']) && !empty($message['timestamp'])) {
                    $message['created_at'] = now()->setTimestamp((int) $message['timestamp'])->format('M j, Y g:i A');
                }
                return $message;
            })
            ->values()
            ->all();
    }

    private function threadsForClient(int $clientId): array
    {
        $serviceMap = [
            'venue_name' => 'Venue',
            'clothes' => 'Clothing',
            'catering' => 'Catering',
            'host' => 'Host',
            'photographer' => 'Photographer',
            'soundsnlights' => 'Sounds & Lights',
        ];
        $events = DB::table('events')->where('user_id', $clientId)->orderByDesc('created_at')->get();
        $threads = [];

        foreach ($events as $event) {
            if (!empty($event->coordinator)) {
                $coordinator = DB::table('users')
                    ->where('role', 'coordinator')
                    ->where('full_name', $event->coordinator)
                    ->first();
                if ($coordinator) {
                    $threads[] = $this->thread($event, (int) $coordinator->user_id, $coordinator->business_name ?: $coordinator->full_name, 'Coordinator');
                }
            }

            foreach ($serviceMap as $column => $role) {
                $serviceName = trim((string) ($event->{$column} ?? ''));
                if ($serviceName === '') {
                    continue;
                }

                $supplier = DB::table('supplier_services as services')
                    ->join('users', 'services.user_id', '=', 'users.user_id')
                    ->whereRaw('LOWER(services.name) = ?', [strtolower($serviceName)])
                    ->orderByDesc('services.service_id')
                    ->select('users.user_id', 'users.full_name', 'users.business_name')
                    ->first();
                if ($supplier) {
                    $threads[] = $this->thread($event, (int) $supplier->user_id, $supplier->business_name ?: $supplier->full_name, $role);
                }
            }
        }

        return $threads;
    }

    private function thread(object $event, int $userId, ?string $name, string $role): array
    {
        return [
            'event_id' => (int) $event->event_id,
            'event_title' => $event->title ?: 'Untitled Event',
            'event_date' => $event->event_date,
            'user_id' => $userId,
            'name' => $name ?: $role,
            'role' => $role,
        ];
    }
}
