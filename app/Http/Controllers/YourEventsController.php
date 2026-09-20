<?php

namespace App\Http\Controllers;

use App\Services\GcashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class YourEventsController extends Controller
{
   private const STATUSES = ['all', 'planning', 'pending', 'ongoing', 'completed', 'cancelled'];


    public function index(Request $request)
    {
        $paymentStatus = $request->query('payment_status');
        if (in_array($paymentStatus, ['success', 'verified', 'paid'], true) && $request->query('event_id') && $request->query('service')) {
            $eventId = (int) $request->query('event_id');
            $serviceType = (string) $request->query('service');
            $event = $this->ownedEvent($eventId);
            $statusColumn = $serviceType === 'sounds_lights' ? 'soundsnlights_status' : $serviceType . '_status';
            $updates = [$statusColumn => 'Paid'];

            if ($serviceType === 'venue') {
                $venueName = trim((string) ($event->venue_name ?? ''));
                $addonStatusMap = [
                    'catering_status' => 'catering',
                    'host_status' => 'host',
                    'photographer_status' => 'photographer',
                    'clothes_status' => 'clothes',
                    'soundsnlights_status' => 'soundsnlights',
                ];

                foreach ($addonStatusMap as $field => $valueField) {
                    $selectedValue = trim((string) ($event->{$valueField} ?? ''));
                    if ($selectedValue !== '' && strtolower($selectedValue) === strtolower($venueName)) {
                        $updates[$field] = 'Paid';
                    }
                }
            }

            DB::table('events')->where('event_id', $event->event_id)->update($updates);

            if (Schema::hasTable('payments')) {
                $payment = DB::table('payments')
                    ->where('event_id', $event->event_id)
                    ->where('user_id', Auth::id())
                    ->whereIn('status', ['pending', 'pending_verification'])
                    ->orderByDesc('payment_id')
                    ->first();

                if ($payment) {
                    DB::table('payments')
                        ->where('payment_id', $payment->payment_id)
                        ->update([
                            'status' => 'verified',
                            'verified_at' => now(),
                        ]);
                }
            }

            $this->markEventPendingWhenServicesArePaid($event->event_id);
        }

        $status = in_array($request->query('status', 'all'), self::STATUSES, true)
            ? $request->query('status', 'all')
            : 'all';

        $baseQuery = DB::table('events')->where('user_id', Auth::id());
        $baseQuery->pluck('event_id')->each(fn ($eventId) => $this->markEventPendingWhenServicesArePaid((int) $eventId));
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

    public function rsvp(Request $request)
    {
        $eventId = (int) ($request->query('event', $request->input('event_id', 0)));
        $event = DB::table('events')->where('event_id', $eventId)->first();
        $invitation = $event ? DB::table('invitations')->where('event_id', $eventId)->first() : null;

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'event_id' => ['required', 'integer', 'min:1'],
                'name' => ['required', 'string', 'max:150'],
                'email' => ['nullable', 'email', 'max:150'],
                'phone' => ['nullable', 'string', 'max:50'],
            ]);

            $event = DB::table('events')->where('event_id', $data['event_id'])->first();
            abort_unless((bool) $event, 404, 'Event not found.');

            $qrCode = 'EI-' . $event->event_id . '-' . strtoupper(bin2hex(random_bytes(4)));
            $guest = DB::table('guests')->where('event_id', $event->event_id)
                ->where(function ($query) use ($data) {
                    if (!empty($data['email'])) {
                        $query->orWhere('email', $data['email']);
                    }
                    if (!empty($data['phone'])) {
                        $query->orWhere('phone', $data['phone']);
                    }
                    $query->orWhere('name', $data['name']);
                })
                ->first();

            if ($guest) {
                DB::table('guests')->where('guest_id', $guest->guest_id)->update([
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'qr_code' => $guest->qr_code ?? $qrCode,
                    'rsvp_status' => 'confirmed',
                    'attended' => $guest->attended ?? 0,
                    'updated_at' => now(),
                ]);
                $guestQr = $guest->qr_code ?? $qrCode;
            } else {
                $guestId = DB::table('guests')->insertGetId([
                    'event_id' => $event->event_id,
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'qr_code' => $qrCode,
                    'rsvp_status' => 'confirmed',
                    'attended' => 0,
                    'created_at' => now(),
                ]);
                $guestQr = DB::table('guests')->where('guest_id', $guestId)->value('qr_code');
            }

            return view('userui.rsvp', [
                'event' => $event,
                'invitation' => $invitation,
                'guest' => (object) [
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'qr_code' => $guestQr,
                    'rsvp_status' => 'confirmed',
                ],
                'success' => true,
            ]);
        }

        abort_unless((bool) $event, 404, 'Event not found.');
        return view('userui.rsvp', [
            'event' => $event,
            'invitation' => $invitation,
            'guest' => null,
            'success' => false,
        ]);
    }

    public function scanner(Request $request, int $eventId)
    {
        $event = $this->ownedEvent($eventId);

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'qr' => ['required', 'string', 'max:100'],
            ]);

            $guest = DB::table('guests')
                ->where('event_id', $eventId)
                ->where('qr_code', $data['qr'])
                ->first();

            if (!$guest) {
                return response()->json(['ok' => false, 'msg' => 'QR code not found for this event.'], 404);
            }

            if ((int) ($guest->attended ?? 0) === 1) {
                return response()->json(['ok' => false, 'msg' => 'Already scanned.'], 409);
            }

            DB::table('guests')->where('guest_id', $guest->guest_id)->update([
                'attended' => 1,
                'scanned_at' => now(),
                'rsvp_status' => 'confirmed',
            ]);

            return response()->json(['ok' => true, 'msg' => 'Welcome ' . $guest->name]);
        }

        $guests = DB::table('guests')->where('event_id', $eventId)->orderBy('name')->get();
        return view('userui.qr-scanner', compact('event', 'guests'));
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

        $venueAddons = [];
        $venueAddOnPrices = 0.0;
        $venueService = null;
        $normalizedVenueName = trim((string) ($event->venue_name ?? ''));
        $venueSupplier = $normalizedVenueName !== '' && Schema::hasTable('supplier_services')
            ? DB::table('supplier_services')->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($normalizedVenueName)])->first()
            : null;

        foreach ($serviceFields as $key => $definition) {
            $name = $event->{$definition['field']} ?? null;
            $serviceStatus = $event->{$definition['status']} ?? 'pending';

            if ($key === 'venue') {
                if ($name || $serviceStatus !== 'pending') {
                    $venueService = [
                        'service_key' => 'venue',
                        'name' => $name ?: $definition['label'],
                        'type' => $definition['label'],
                        'status' => $serviceStatus,
                        'raw_status' => $serviceStatus,
                        'price' => $venueSupplier?->price ?? 0,
                        'note' => $event->{$definition['note']} ?? null,
                        'supplier_user_id' => $venueSupplier?->user_id,
                    ];
                }
                continue;
            }

            if ($name || $serviceStatus !== 'pending') {
                $supplier = Schema::hasTable('supplier_services')
                    ? DB::table('supplier_services')->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $name))])->first()
                    : null;
                $price = $supplier?->price;
                if ($supplier && strtolower((string) $supplier->category) === 'venue' && $key !== 'venue') {
                    $addonPriceColumns = [
                        'catering' => 'venueaddons_price1',
                        'clothes' => 'venueaddons_price2',
                        'host' => 'venueaddons_price3',
                        'photographer' => 'venueaddons_price4',
                        'sounds_lights' => 'venueaddons_price5',
                    ];
                    $priceColumn = $addonPriceColumns[$key] ?? null;
                    $price = $priceColumn ? ($supplier->{$priceColumn} ?? 0) : 0;
                }

                $serviceEntry = [
                    'service_key' => $key,
                    'name' => $name ?: $definition['label'],
                    'type' => $definition['label'],
                    'status' => $serviceStatus,
                    'raw_status' => $serviceStatus,
                    'price' => $price,
                    'note' => $event->{$definition['note']} ?? null,
                    'supplier_user_id' => $supplier?->user_id,
                ];

                if ($normalizedVenueName !== '' && strtolower(trim((string) $name)) === strtolower($normalizedVenueName)) {
                    $venueAddons[] = $definition['label'];
                    $venueAddOnPrices += (float) ($price ?? 0);
                    continue;
                }

                $services[] = $serviceEntry;
            }
        }

        if ($venueService) {
            $combinedLabels = array_values(array_unique(array_filter($venueAddons, fn ($label) => $label !== '')));
            if (!empty($combinedLabels)) {
                $addonLabelText = implode(' + ', $combinedLabels);
                $venueService['name'] = $venueService['name'] . ' + ' . $addonLabelText;
                $venueService['type'] = 'Venue + Add-ons';
                $venueService['price'] = ((float) ($venueService['price'] ?? 0)) + $venueAddOnPrices;
            }
            $services[] = $venueService;
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

    public function reselect(Request $request, int $eventId)
    {
        $data = $request->validate([
            'service_type' => ['required', 'in:venue,catering,host,sounds_lights,photographer,clothes,coordinator'],
            'service_name' => ['nullable', 'string', 'max:255'],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['string'],
        ]);

        $event = $this->ownedEvent($eventId);
        $serviceFields = [
            'venue' => ['value_field' => 'venue_name', 'status_field' => 'venue_status', 'note_field' => 'venue_note'],
            'catering' => ['value_field' => 'catering', 'status_field' => 'catering_status', 'note_field' => 'catering_note'],
            'host' => ['value_field' => 'host', 'status_field' => 'host_status', 'note_field' => 'host_note'],
            'sounds_lights' => ['value_field' => 'soundsnlights', 'status_field' => 'soundsnlights_status', 'note_field' => 's&l_note'],
            'photographer' => ['value_field' => 'photographer', 'status_field' => 'photographer_status', 'note_field' => 'photographer_note'],
            'clothes' => ['value_field' => 'clothes', 'status_field' => 'clothes_status', 'note_field' => 'clothes_note'],
            'coordinator' => ['value_field' => 'coordinator', 'status_field' => 'coordinator_status', 'note_field' => 'coordinator_proposal'],
        ];

        $service = $serviceFields[$data['service_type']];

        $updates = [];

        if (!empty($data['service_name'])) {
            $updates[$service['value_field']] = trim((string) $data['service_name']);
            $updates[$service['status_field']] = 'pending';

            if ($service['note_field'] === 'coordinator_proposal') {
                $updates[$service['note_field']] = null;
            } elseif ($service['note_field'] !== 's&l_note') {
                $updates[$service['note_field']] = null;
            }

            // If this is a venue selection with add-ons, clear and update addon fields
            if ($data['service_type'] === 'venue') {
                $venueName = trim((string) $data['service_name']);

                // Add-ons are optional venue-provided services. Preserve independently
                // selected services and update only the add-ons explicitly chosen here.
                $selectedAddons = $data['addons'] ?? [];
                if (!empty($selectedAddons)) {
                    foreach ($selectedAddons as $addon) {
                        $addonField = $addon;
                        if (in_array($addon, ['sounds_lights', 'soundsnlights', 'sounds and lights', 'sounds & lights'], true)) {
                            $addonField = 'soundsnlights';
                        } elseif (in_array($addon, ['clothes', 'clothing', 'attire', 'styling'], true)) {
                            $addonField = 'clothes';
                        }

                        // Set the addon field to the venue name (indicating it was selected as a venue add-on)
                        // and match its status to the venue status
                        $updates[$addonField] = $venueName;
                        $updates[$addonField . '_status'] = 'pending';  // Match the venue status
                    }
                }
            }

            DB::table('events')
                ->where('event_id', $event->event_id)
                ->where('user_id', Auth::id())
                ->update($updates);
        }

        return response()->json(['success' => true, 'message' => 'The declined service has been replaced for reselection.']);
    }

    public function pay(Request $request, int $eventId)
    {
        $data = $request->validate([
            'service_type' => ['required', 'in:venue,catering,host,sounds_lights,photographer,clothes,coordinator'],
            'payment_method' => ['required', 'in:cash,online'],
            'amount' => ['nullable', 'numeric', 'min:1'],
            'payment_status' => ['nullable', 'in:success,verified,paid,pending'],
        ]);
        $event = $this->ownedEvent($eventId);
        $statusColumn = $data['service_type'] === 'sounds_lights' ? 'soundsnlights_status' : $data['service_type'] . '_status';
        $normalizedPaymentStatus = $data['payment_status'] ?? null;

        if (in_array($normalizedPaymentStatus, ['success', 'verified', 'paid'], true)) {
            $paymentStatusValue = 'Paid';
            $storedPaymentStatus = 'paid';
        } else {
            $paymentStatusValue = 'Pending Confirmation';
            $storedPaymentStatus = $data['payment_method'] === 'online' ? 'pending_verification' : 'pending_confirmation';
        }

        $updates = [
            $statusColumn => $paymentStatusValue,
            'payment_method' => $data['payment_method'],
            'payment_status' => $storedPaymentStatus,
        ];

        if ($data['service_type'] === 'venue') {
            $venueName = trim((string) ($event->venue_name ?? ''));
            $addonStatusMap = [
                'catering_status' => 'catering',
                'host_status' => 'host',
                'photographer_status' => 'photographer',
                'clothes_status' => 'clothes',
                'soundsnlights_status' => 'soundsnlights',
            ];

            foreach ($addonStatusMap as $statusField => $fieldName) {
                $selectedValue = trim((string) ($event->{$fieldName} ?? ''));
                if ($selectedValue !== '' && strtolower($selectedValue) === strtolower($venueName)) {
                    $updates[$statusField] = $paymentStatusValue;
                }
            }
        }

        $paymentMethod = $data['payment_method'];
        $amount = (float) ($data['amount'] ?? 0);
        $gcashResponse = null;

        if ($paymentMethod === 'online') {
            $amount = $amount > 0 ? $amount : 500;
            $externalId = 'ei-event-' . $event->event_id . '-' . $data['service_type'] . '-' . now()->timestamp;
            $gcashResponse = app(GcashService::class)->createPayment([
                'external_id' => $externalId,
                'amount' => $amount,
                'description' => 'EventIntel payment for ' . ucfirst(str_replace('_', ' ', $data['service_type'])) . ' service',
                'currency' => 'PHP',
                'metadata' => [
                    'event_id' => $event->event_id,
                    'service_type' => $data['service_type'],
                    'user_id' => Auth::id(),
                ],
                'success_url' => route('your.events', ['payment_status' => 'success', 'event_id' => $eventId, 'service' => $data['service_type']]),
                'cancel_url' => route('your.events', ['payment_status' => 'cancelled', 'event_id' => $eventId, 'service' => $data['service_type']]),
            ]);

            if (Schema::hasTable('payments')) {
                DB::table('payments')->updateOrInsert(
                    ['reference_no' => $externalId],
                    [
                        'event_id' => $event->event_id,
                        'user_id' => Auth::id(),
                        'amount' => $amount,
                        'status' => 'pending',
                        'verified_at' => null,
                        'created_at' => now(),
                    ]
                );
            }
        }

        DB::table('events')->where('event_id', $event->event_id)->update($updates);
        $this->markEventPendingWhenServicesArePaid($event->event_id);

        return response()->json([
            'success' => true,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatusValue,
            'gcash' => $gcashResponse,
        ]);
    }

    private function markEventPendingWhenServicesArePaid(int $eventId): void
    {
        $event = DB::table('events')->where('event_id', $eventId)->first();
        if (!$event) {
            return;
        }

        $services = [
            ['name' => 'venue_name', 'status' => 'venue_status'],
            ['name' => 'catering', 'status' => 'catering_status'],
            ['name' => 'host', 'status' => 'host_status'],
            ['name' => 'soundsnlights', 'status' => 'soundsnlights_status'],
            ['name' => 'photographer', 'status' => 'photographer_status'],
            ['name' => 'clothes', 'status' => 'clothes_status'],
            ['name' => 'coordinator', 'status' => 'coordinator_status'],
        ];


        $selectedServices = array_filter($services, fn (array $service): bool =>
            trim((string) ($event->{$service['name']} ?? '')) !== ''
        );

        $allServicesPaid = $selectedServices !== [] && array_reduce(
            $selectedServices,
            fn (bool $allPaid, array $service): bool => $allPaid
                && strtolower(trim((string) ($event->{$service['status']} ?? ''))) === 'paid',
            true
        );

        if ($allServicesPaid) {
            $eventStatus = !empty($event->event_date)
                && \Illuminate\Support\Carbon::parse($event->event_date)->isBefore(today())
                ? 'completed'
                : 'pending';

            DB::table('events')->where('event_id', $eventId)->update(['status' => $eventStatus]);
        }
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
