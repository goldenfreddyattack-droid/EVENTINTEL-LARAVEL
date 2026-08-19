<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupplierMessagesController extends Controller
{
    public function __construct(private FirebaseService $firebase)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'supplier') {
                abort(403, 'Unauthorized. Supplier access only.');
            }

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $supplierId = (int) Auth::id();
        $threads = $this->threadsForSupplier($supplierId);
        $selectedEventId = (int) $request->integer('event_id');
        $selectedUserId = (int) $request->integer('user_id');

        if ($selectedUserId > 0 && $selectedEventId === 0) {
            $selectedThread = collect($threads)->firstWhere('user_id', $selectedUserId);
            $selectedEventId = (int) ($selectedThread['event_id'] ?? 0);
        }

        if ($selectedEventId === 0 && $threads !== []) {
            $selectedEventId = $threads[0]['event_id'];
            $selectedUserId = $threads[0]['user_id'];
        }

        if ($selectedEventId > 0 && $selectedUserId === 0) {
            $selectedThread = collect($threads)->firstWhere('event_id', $selectedEventId);
            $selectedUserId = (int) ($selectedThread['user_id'] ?? 0);
        }

        $selectedThread = collect($threads)->first(function ($thread) use ($selectedEventId, $selectedUserId) {
            return $thread['event_id'] === $selectedEventId && $thread['user_id'] === $selectedUserId;
        });

        // Firebase is loaded asynchronously by the view so the page can render immediately.
        foreach ($threads as &$thread) {
            $thread['unread'] = 0;
        }
        unset($thread);

        return view('supplier.messages', [
            'threads' => $threads,
            'messages' => [],
            'selectedThread' => $selectedThread,
            'selectedEventId' => $selectedEventId,
            'selectedUserId' => $selectedUserId,
            'lastMessageId' => 0,
            'globalUnread' => 0,
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'min:1'],
            'recipient_id' => ['required', 'integer', 'min:1'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $supplierId = (int) Auth::id();
        $thread = collect($this->threadsForSupplier($supplierId))->first(function ($item) use ($data) {
            return $item['event_id'] === (int) $data['event_id'] && $item['user_id'] === (int) $data['recipient_id'];
        });

        abort_unless($thread, 403);

        $messageId = (int) round(microtime(true) * 1000);
        $messageBody = trim($data['message']);
        $senderName = Auth::user()->full_name ?? 'Supplier';
        if (!$this->firebase->saveMessage($data['event_id'], $supplierId, $data['recipient_id'], $messageBody, $senderName, $messageId)) {
            return back()->withInput()->with('message_error', 'Message could not be sent.');
        }

        return redirect()->route('supplier.messages', [
            'event_id' => $data['event_id'],
            'user_id' => $data['recipient_id'],
        ])->with('sent_message', [
            'message_id' => $messageId,
            'sender_id' => $supplierId,
            'full_name' => $senderName,
            'body' => $messageBody,
            'created_at' => now()->format('M j, Y g:i A'),
        ]);
    }

    public function api(Request $request)
    {
        $userId = (int) Auth::id();
        $action = $request->string('action')->toString();
        $eventId = (int) $request->integer('event_id');
        $otherUserId = (int) $request->integer('other_user_id');

        if ($action === 'poll') {
            abort_unless($eventId > 0 && $otherUserId > 0, 422);
            $lastId = (float) $request->input('last_id', 0);
            $messages = collect($this->firebase->getMessages($eventId, $userId, $otherUserId))
                ->filter(function ($message) use ($lastId, $userId, $otherUserId) {
                    $messageId = (float) ($message['message_id'] ?? $message['timestamp'] ?? 0);
                    $senderId = (int) ($message['sender_id'] ?? 0);
                    $recipientId = $message['recipient_id'] ?? $message['receiver_id'] ?? null;
                    $recipientId = $recipientId === null || $recipientId === '' ? null : (int) $recipientId;
                    $validDirection = ($senderId === $otherUserId && ($recipientId === null || $recipientId === $userId))
                        || ($senderId === $userId && ($recipientId === null || $recipientId === $otherUserId));

                    return $messageId > $lastId && $validDirection;
                })
                ->values();

            return response()->json(['messages' => $messages]);
        }

        if ($action === 'mark_read') {
            if ($eventId > 0 && $otherUserId > 0) {
                $this->firebase->markMessagesAsRead($eventId, $userId, $otherUserId, $userId);
            }

            return response()->json(['success' => true]);
        }

        if ($action === 'thread_unread') {
            return response()->json(['unread' => $this->unreadCount($eventId, $userId, $otherUserId)]);
        }

        return response()->json(['error' => 'Unknown action'], 422);
    }

    private function threadsForSupplier(int $supplierId): array
    {
        $serviceMap = [
            'venue_name' => 'Venue',
            'clothes' => 'Clothing',
            'catering' => 'Catering',
            'host' => 'Host',
            'photographer' => 'Photographer',
            'soundsnlights' => 'Sounds & Lights',
        ];
        $serviceNames = DB::table('supplier_services')->where('user_id', $supplierId)->pluck('name')->map(fn ($name) => strtolower(trim($name)))->all();
        $threads = [];

        if ($serviceNames === []) {
            return [];
        }

        foreach ($serviceMap as $column => $roleLabel) {
            $events = DB::table('events as e')
                ->join('users as u', 'e.user_id', '=', 'u.user_id')
                ->select("e.event_id", "e.title", "e.event_date", "e.user_id as client_id", "u.full_name as client_name", "e.$column as service_name", 'e.coordinator')
                ->whereIn(DB::raw("LOWER(e.$column)"), $serviceNames)
                ->orderByDesc('e.created_at')
                ->get();

            foreach ($events as $event) {
                $threads[] = [
                    'event_id' => (int) $event->event_id,
                    'event_title' => $event->title ?: 'Untitled Event',
                    'event_date' => $event->event_date,
                    'user_id' => (int) $event->client_id,
                    'name' => $event->client_name ?: 'Client',
                    'role' => 'Client',
                ];

                if (!empty($event->coordinator)) {
                    $coordinator = DB::table('users')->where('role', 'coordinator')->where('full_name', $event->coordinator)->first();
                    if ($coordinator && (int) $coordinator->user_id !== $supplierId) {
                        $threads[] = [
                            'event_id' => (int) $event->event_id,
                            'event_title' => $event->title ?: 'Untitled Event',
                            'event_date' => $event->event_date,
                            'user_id' => (int) $coordinator->user_id,
                            'name' => $coordinator->business_name ?: $coordinator->full_name,
                            'role' => 'Coordinator',
                        ];
                    }
                }
            }
        }

        return $threads;
    }

    private function unreadCount(int $eventId, int $supplierId, int $otherUserId): int
    {
        return collect($this->firebase->getMessages($eventId, $supplierId, $otherUserId))
            ->filter(fn ($message) => !isset($message['read_by'][(string) $supplierId]) && !isset($message['read_by'][$supplierId]))
            ->count();
    }

    private function formatTimestamp(mixed $timestamp): string
    {
        return $timestamp ? now()->setTimestamp((int) $timestamp)->format('M j, Y g:i A') : '';
    }
}
