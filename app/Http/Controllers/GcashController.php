<?php

namespace App\Http\Controllers;

use App\Services\GcashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GcashController extends Controller
{
    public function create(Request $request, GcashService $gcashService)
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer'],
            'service_type' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'success_url' => ['nullable', 'url'],
            'cancel_url' => ['nullable', 'url'],
        ]);

        $successUrl = $data['success_url'] ?? route('your.events', ['payment_status' => 'success', 'event_id' => $data['event_id'], 'service' => $data['service_type']]);
        $cancelUrl = $data['cancel_url'] ?? route('your.events', ['payment_status' => 'cancelled', 'event_id' => $data['event_id'], 'service' => $data['service_type']]);

        $payload = [
            'external_id' => 'ei-gcash-' . $data['event_id'] . '-' . $data['service_type'] . '-' . now()->timestamp,
            'amount' => (float) $data['amount'],
            'description' => 'EventIntel GCash payment for ' . ucfirst($data['service_type']),
            'currency' => 'PHP',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'event_id' => $data['event_id'],
                'service_type' => $data['service_type'],
            ],
        ];

        $payment = $gcashService->createPayment($payload);

        return response()->json([
            'success' => true,
            'payment' => $payment,
        ]);
    }

    public function verify(string $reference, GcashService $gcashService)
    {
        $result = $gcashService->verifyPayment($reference);

        return response()->json($result);
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('x-signature');
        $secret = config('services.gcash.webhook_secret');

        if ($secret && $signature) {
            $expected = hash_hmac('sha256', $request->getContent(), $secret);
            if (! hash_equals($expected, $signature)) {
                return response()->json(['success' => false], 403);
            }
        }

        $status = strtolower((string) ($payload['status'] ?? ''));
        $reference = (string) ($payload['external_id'] ?? $payload['reference'] ?? '');

        if ($reference !== '') {
            DB::table('payments')->where('reference_no', $reference)->update([
                'status' => $status === 'paid' ? 'verified' : 'pending',
                'verified_at' => $status === 'paid' ? now() : null,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
