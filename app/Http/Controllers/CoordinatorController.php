<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Services\FirebaseService;

class CoordinatorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user()->role === 'coordinator', 403, 'Coordinator access only.');
            return $next($request);
        });
    }

    public function dashboard()
    {
        $name = Auth::user()->full_name;
        $events = DB::table('events')->where('coordinator', $name)->orderByDesc('event_id')->limit(4)->get();
        $pending = DB::table('events')->where('coordinator', $name)->where('coordinator_status', 'pending')->count();
        $ongoing = DB::table('events')->where('coordinator', $name)->whereIn('coordinator_status', ['accepted', 'proposal_sent', 'Payment Pending', 'Paid'])->count();
        $totalSuppliers = DB::table('supplier_services')->distinct('user_id')->count('user_id');
        return view('coordinator.dashboard', compact('events', 'pending', 'ongoing', 'totalSuppliers'));
    }

    public function events()
    {
        $events = DB::table('events')->leftJoin('users', 'events.user_id', '=', 'users.user_id')
            ->where('events.coordinator', Auth::user()->full_name)
            ->select('events.*', 'events.event_date as event_date', 'users.full_name as client_name', 'users.user_id as client_id')
            ->orderByDesc('events.created_at')->paginate(10);
        return view('coordinator.assigned-events', compact('events'));
    }

    public function updateEvent(Request $request, $eventId)
    {
        $action = $request->validate(['action' => ['required', 'in:accepted,declined,paid']])['action'];
        $status = $action === 'paid' ? 'Paid' : $action;
        DB::table('events')->where('event_id', $eventId)->where('coordinator', Auth::user()->full_name)->update(['coordinator_status' => $status]);
        return back()->with('success', 'Event status updated.');
    }

    public function packages()
    {
        $packages = Schema::hasTable('coordinator_packages') ? DB::table('coordinator_packages')->where('coordinator_id', Auth::id())->orderByDesc('is_featured')->orderBy('price')->get() : collect();
        return view('coordinator.packages', compact('packages'));
    }

    public function storePackage(Request $request)
    {
        $data = $request->validate(['package_id' => ['nullable', 'integer'], 'name' => ['required', 'string', 'max:150'], 'price' => ['required', 'numeric', 'min:0.01'], 'description' => ['nullable', 'string'], 'inclusions' => ['nullable', 'string']]);
        $values = ['name' => $data['name'], 'price' => $data['price'], 'description' => $data['description'] ?? null, 'inclusions' => implode('|', array_filter(array_map('trim', preg_split('/[\r\n]+/', $data['inclusions'] ?? '')))), 'is_featured' => $request->boolean('is_featured')];
        if (!empty($data['package_id'])) DB::table('coordinator_packages')->where('package_id', $data['package_id'])->where('coordinator_id', Auth::id())->update($values);
        else DB::table('coordinator_packages')->insert($values + ['coordinator_id' => Auth::id()]);
        return redirect()->route('coordinator.packages')->with('success', 'Package saved successfully.');
    }

    public function deletePackage($id)
    {
        DB::table('coordinator_packages')->where('package_id', $id)->where('coordinator_id', Auth::id())->delete();
        return back()->with('success', 'Package deleted.');
    }

    public function profile()
    {
        $user = Auth::user();
        $profile = Schema::hasTable('coordinator_profile') ? DB::table('coordinator_profile')->where('coordinator_id', Auth::id())->first() : null;
        $gallery = Schema::hasTable('coordinator_gallery') ? DB::table('coordinator_gallery')->where('coordinator_id', Auth::id())->orderByDesc('created_at')->get() : collect();
        return view('coordinator.profile', compact('user', 'profile', 'gallery'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate(['business_name' => ['nullable', 'string', 'max:150'], 'business_address' => ['nullable', 'string'], 'about' => ['nullable', 'string'], 'services' => ['nullable', 'string']]);
        DB::table('users')->where('user_id', Auth::id())->update(['business_name' => $data['business_name'] ?? null, 'business_address' => $data['business_address'] ?? null]);
        if (Schema::hasTable('coordinator_profile')) DB::table('coordinator_profile')->updateOrInsert(['coordinator_id' => Auth::id()], ['about' => $data['about'] ?? null, 'services' => str_replace("\n", '|', $data['services'] ?? '')]);
        return back()->with('success', 'Profile updated successfully.');
    }

    public function suppliers()
    {
        $names = DB::table('events')->where('coordinator', Auth::user()->full_name)->get(['venue_name','clothes','catering','host','photographer','soundsnlights'])->flatMap(fn ($e) => collect((array) $e))->filter()->unique()->values();
        $suppliers = DB::table('supplier_services')->join('users', 'supplier_services.user_id', '=', 'users.user_id')->whereIn('supplier_services.name', $names)->select('supplier_services.*', 'users.business_name', 'users.full_name', 'users.business_address')->get();
        return view('coordinator.suppliers', compact('suppliers'));
    }

    public function proposals(Request $request)
    {
        $events = DB::table('events')->where('coordinator', Auth::user()->full_name)->orderByDesc('created_at')->get();
        return view('coordinator.proposals', ['events' => $events, 'selectedEvent' => $events->firstWhere('event_id', (int) $request->get('event_id'))]);
    }
    
    public function storeProposal(Request $request)
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer'],
            'venue' => ['nullable', 'string'], 
            'catering' => ['nullable', 'string'], 
            'clothing' => ['nullable', 'string'],
            'decorations' => ['nullable', 'string'], 
            'host' => ['nullable', 'string'], 
            'photography' => ['nullable', 'string'],
            'videography' => ['nullable', 'string'], 
            'timeline' => ['nullable', 'string'], 
            'cost_breakdown' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'], 
            'total_quotation' => ['nullable', 'numeric', 'min:0'],
        ]);
        $event = DB::table('events')->where('event_id', $data['event_id'])->where('coordinator', Auth::user()->full_name)->first();
        abort_unless((bool) $event, 404);
        $values = collect($data)->except('event_id')->all();
        $values['status'] = $request->has('send_now') ? 'sent' : 'draft';
        DB::table('coordinator_proposals')->updateOrInsert(
            ['event_id' => $event->event_id, 'coordinator_id' => Auth::id()],
            $values + ['client_id' => $event->user_id]
        );
        DB::table('events')->where('event_id', $event->event_id)->update(['coordinator_status' => $values['status'] === 'sent' ? 'proposal_sent' : 'accepted']);
        return redirect()->route('coordinator.proposals', ['event_id' => $event->event_id])->with('success', 'Proposal saved successfully.');
    }

    public function messages(Request $request, FirebaseService $firebase)
    {
        $threads = $this->messageThreads();
        $eventId = (int) $request->integer('event_id');
        $otherUserId = (int) $request->integer('user_id');
        if ($otherUserId > 0 && $eventId === 0) {
            $thread = collect($threads)->firstWhere('user_id', $otherUserId);
            $eventId = (int) ($thread['event_id'] ?? 0);
        }
        if ($eventId === 0 && $threads !== []) {
            $eventId = $threads[0]['event_id'];
            $otherUserId = $threads[0]['user_id'];
        }
        if ($eventId > 0 && $otherUserId === 0) {
            $thread = collect($threads)->firstWhere('event_id', $eventId);
            $otherUserId = (int) ($thread['user_id'] ?? 0);
        }
        $selectedThread = collect($threads)->first(fn ($thread) => $thread['event_id'] === $eventId && $thread['user_id'] === $otherUserId);
        if ($request->isMethod('post')) {
            $data = $request->validate(['event_id' => ['required', 'integer'], 'recipient_id' => ['required', 'integer', 'min:1'], 'message' => ['required', 'string', 'max:5000']]);
            abort_unless(collect($threads)->contains(fn ($thread) => $thread['event_id'] === (int) $data['event_id'] && $thread['user_id'] === (int) $data['recipient_id']), 403);
            abort_unless($firebase->saveMessage((int) $data['event_id'], Auth::id(), (int) $data['recipient_id'], trim($data['message']), Auth::user()->full_name), 502);
            return redirect()->route('coordinator.messages', ['event_id' => $data['event_id'], 'user_id' => $data['recipient_id'] ?? 0]);
        }
        $messages = [];
        $lastMessageId = 0;
        if ($selectedThread) {
            $messages = collect($firebase->getMessages($eventId, Auth::id(), $otherUserId))
                ->filter(fn ($message) => trim((string) ($message['message'] ?? $message['body'] ?? '')) !== '')
                ->values()
                ->all();
            $lastMessageId = (float) (collect($messages)->last()['message_id'] ?? collect($messages)->last()['timestamp'] ?? 0);
            $firebase->markMessagesAsRead($eventId, Auth::id(), $otherUserId, Auth::id());
        }
        return view('coordinator.messages', compact('threads', 'messages', 'selectedThread', 'eventId', 'otherUserId', 'lastMessageId'));
    }

    public function messageApi(Request $request, FirebaseService $firebase)
    {
        $eventId = (int) $request->integer('event_id');
        $otherUserId = (int) $request->integer('other_user_id');
        abort_unless(collect($this->messageThreads())->contains(fn ($thread) => $thread['event_id'] === $eventId && $thread['user_id'] === $otherUserId), 403);
        if ($request->string('action')->toString() === 'mark_read') {
            $firebase->markMessagesAsRead($eventId, Auth::id(), $otherUserId, Auth::id());
            return response()->json(['success' => true]);
        }
        $lastId = (float) $request->input('last_id', 0);
        $messages = collect($firebase->getMessages($eventId, Auth::id(), $otherUserId))
            ->filter(fn ($message) => trim((string) ($message['message'] ?? $message['body'] ?? '')) !== '')
            ->filter(fn ($message) => (float) ($message['message_id'] ?? $message['timestamp'] ?? 0) > $lastId)
            ->values();
        return response()->json(['messages' => $messages]);
    }

    private function messageThreads(): array
    {
        $coordinatorId = (int) Auth::id();
        $coordinatorName = Auth::user()->full_name;
        $events = DB::table('events')->where('coordinator', $coordinatorName)->orderByDesc('created_at')->get();
        $threads = [];
        foreach ($events as $event) {
            if ($event->user_id) {
                $client = DB::table('users')->where('user_id', $event->user_id)->first();
                if ($client && (int) $client->user_id !== $coordinatorId) {
                    $threads[] = ['event_id' => (int) $event->event_id, 'event_title' => $event->title ?: 'Untitled Event', 'event_date' => $event->event_date, 'user_id' => (int) $client->user_id, 'name' => $client->full_name ?: 'Client', 'role' => 'Client', 'unread' => 0];
                }
            }
            foreach (['venue_name', 'clothes', 'catering', 'host', 'photographer', 'soundsnlights'] as $column) {
                $serviceName = trim((string) ($event->{$column} ?? ''));
                if ($serviceName === '') continue;
                $supplier = DB::table('supplier_services')->join('users', 'supplier_services.user_id', '=', 'users.user_id')->whereRaw('LOWER(supplier_services.name) = LOWER(?)', [$serviceName])->select('users.user_id', 'users.full_name', 'users.business_name')->first();
                if ($supplier && (int) $supplier->user_id !== $coordinatorId) {
                    $threads[] = ['event_id' => (int) $event->event_id, 'event_title' => $event->title ?: 'Untitled Event', 'event_date' => $event->event_date, 'user_id' => (int) $supplier->user_id, 'name' => $supplier->business_name ?: $supplier->full_name, 'role' => 'Supplier', 'unread' => 0];
                }
            }
        }
        return $threads;
    }

    public function reports() { return view('coordinator.reports'); }

    public function settings() { return view('coordinator.settings', ['user' => Auth::user()]); }

    public function updateSettings(Request $request)
    {
        $data = $request->validate(['full_name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email'], 'business_name' => ['nullable', 'string', 'max:150']]);
        DB::table('users')->where('user_id', Auth::id())->update($data);
        return back()->with('success', 'Settings saved.');
    }
}
