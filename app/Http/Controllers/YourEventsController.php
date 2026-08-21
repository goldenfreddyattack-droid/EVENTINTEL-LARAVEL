<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class YourEventsController extends Controller
{
    private const STATUSES = ['all', 'pending', 'planning', 'ongoing', 'completed', 'cancelled'];

    public function index(Request $request)
    {
        $status = in_array($request->query('status', 'all'), self::STATUSES, true)
            ? $request->query('status', 'all')
            : 'all';

        $baseQuery = DB::table('events')->where('user_id', Auth::id());
        $counts = collect(self::STATUSES)->mapWithKeys(fn (string $key) => [
            $key => $key === 'all'
                ? (clone $baseQuery)->count()
                : (clone $baseQuery)->where('status', $key)->count(),
        ])->all();

        $events = (clone $baseQuery)
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderByDesc('event_date')
            ->orderByDesc('event_time')
            ->paginate(12)
            ->withQueryString();

        return view('userui.your-events', compact('events', 'status', 'counts'));
    }

    public function map(int $eventId)
    {
        $event = $this->ownedEvent($eventId);

        $event->venue_name = $event->venue_name ?: 'The Grand Pavilion';
        $event->venue_address = $event->venue_address ?: 'Apalit, Pampanga';
        $event->latitude = $event->latitude ?: '14.9533';
        $event->longitude = $event->longitude ?: '120.7690';

        return view('userui.map', compact('event'));
    }

    public function guests(Request $request, int $eventId)
    {
        $event = $this->ownedEvent($eventId);

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:150'],
                'email' => ['nullable', 'email', 'max:150'],
                'phone' => ['nullable', 'string', 'max:50'],
            ]);

            DB::table('guests')->insert([
                'event_id' => $eventId,
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'qr_code' => 'EI-' . $eventId . '-' . strtoupper(bin2hex(random_bytes(4))),
            ]);

            return redirect()->route('your.events.guests', $eventId)->with('success', 'Guest added successfully.');
        }

        $guests = DB::table('guests')->where('event_id', $eventId)->orderBy('name')->get();

        return view('userui.guests', compact('event', 'guests'));
    }

    public function invitation(Request $request, int $eventId)
    {
        $event = $this->ownedEvent($eventId);
        $hasTemplateColumn = Schema::hasColumn('invitations', 'template');

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'template' => ['required', 'in:Classic,Wedding,Birthday,Corporate,Elegant'],
                'title' => ['required', 'string', 'max:150'],
                'message' => ['required', 'string', 'max:5000'],
                'theme_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
                'font_style' => ['required', 'in:Segoe UI,Georgia,Arial'],
                'button_text' => ['required', 'string', 'max:100'],
                'background' => ['nullable', 'image', 'max:5120'],
            ]);

            $values = [
                'title' => $data['title'],
                'message' => $data['message'],
                'theme_color' => $data['theme_color'],
                'font_style' => $data['font_style'],
                'button_text' => $data['button_text'],
            ];
            if ($hasTemplateColumn) {
                $values['template'] = $data['template'];
            }
            if ($request->hasFile('background')) {
                $values['background_image'] = $request->file('background')->store('invitations', 'public');
            }

            DB::table('invitations')->updateOrInsert(['event_id' => $eventId], $values);

            return redirect()->route('your.events.invitation', $eventId)->with('success', 'Invitation saved successfully.');
        }

        $invitation = DB::table('invitations')->where('event_id', $eventId)->first();
        $template = isset($invitation->template) && $invitation->template
            ? $invitation->template
            : $this->defaultInvitationTemplate($event->event_type);
        $invitation = (object) array_merge([
            'title' => "You're Invited",
            'message' => 'Please RSVP',
            'theme_color' => '#f3c547',
            'font_style' => 'Segoe UI',
            'button_text' => 'Confirm RSVP',
            'background_image' => null,
            'template' => $template,
        ], $invitation ? (array) $invitation : []);
        $invitation->template = $template;

        return view('userui.invitation-builder', compact('event', 'invitation'));
    }

    public function status(int $eventId)
    {
        $event = $this->ownedEvent($eventId);
        $services = [];
        $serviceFields = [
            'venue' => ['label' => 'Venue', 'field' => 'venue_name', 'status' => 'venue_status', 'note' => 'venue_note'],
            'catering' => ['label' => 'Catering/Food', 'field' => 'catering', 'status' => 'catering_status', 'note' => 'catering_note'],
            'host' => ['label' => 'Host/MC', 'field' => 'host', 'status' => 'host_status', 'note' => 'host_note'],
            'sounds_lights' => ['label' => 'Sounds & Lights', 'field' => 'soundsnlights', 'status' => 'soundsnlights_status', 'note' => 's&l_note'],
            'photographer' => ['label' => 'Photographer', 'field' => 'photographer', 'status' => 'photographer_status', 'note' => 'photographer_note'],
            'clothes' => ['label' => 'Clothing/Attire', 'field' => 'clothes', 'status' => 'clothes_status', 'note' => 'clothes_note'],
        ];

        foreach ($serviceFields as $key => $definition) {
            $name = $event->{$definition['field']} ?? null;
            $serviceStatus = $event->{$definition['status']} ?? 'pending';
            if ($name || $serviceStatus !== 'pending') {
                $supplier = Schema::hasTable('supplier_services')
                    ? DB::table('supplier_services')->where('name', $name)->first()
                    : null;
                $services[] = [
                    'service_key' => $key,
                    'name' => $name ?: $definition['label'],
                    'type' => $definition['label'],
                    'status' => $serviceStatus,
                    'raw_status' => $serviceStatus,
                    'price' => $supplier?->price,
                    'note' => $event->{$definition['note']} ?? null,
                    'supplier_user_id' => $supplier?->user_id,
                ];
            }
        }

        if ($event->coordinator) {
            $services[] = [
                'service_key' => 'coordinator',
                'name' => $event->coordinator,
                'type' => 'Coordinator',
                'status' => $event->coordinator_status ?? 'pending',
                'raw_status' => $event->coordinator_status ?? 'pending',
                'price' => null,
                'note' => $event->coordinator_proposal,
                'supplier_user_id' => null,
            ];
        }

        return response()->json(['services' => $services, 'coordinator_proposal' => $event->coordinator_proposal]);
    }

    public function pay(Request $request, int $eventId)
    {
        $data = $request->validate([
            'service_type' => ['required', 'in:venue,catering,host,sounds_lights,photographer,clothes,coordinator'],
            'payment_method' => ['required', 'in:cash,online'],
        ]);
        $event = $this->ownedEvent($eventId);
        $statusColumn = $data['service_type'] === 'sounds_lights' ? 'soundsnlights_status' : $data['service_type'] . '_status';

        DB::table('events')->where('event_id', $event->event_id)->update([
            $statusColumn => 'Payment Pending',
            'payment_method' => $data['payment_method'],
            'payment_status' => 'pending',
        ]);

        return response()->json(['success' => true]);
    }

    private function ownedEvent(int $eventId): object
    {
        $event = DB::table('events')->where('event_id', $eventId)->where('user_id', Auth::id())->first();
        abort_unless((bool) $event, 404);
        return $event;
    }

    private function defaultInvitationTemplate(?string $eventType): string
    {
        return match (strtolower((string) $eventType)) {
            'wedding' => 'Wedding',
            'birthday' => 'Birthday',
            'corporate' => 'Corporate',
            'debut' => 'Elegant',
            default => 'Classic',
        };
    }
}
