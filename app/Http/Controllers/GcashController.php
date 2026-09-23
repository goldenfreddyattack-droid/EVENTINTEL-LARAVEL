<?php

namespace App\Http\Controllers;

use App\Services\GcashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                'event_id' => (string) $data['event_id'],
                'service_type' => $data['service_type'],
            ],
        ];

        $payment = $gcashService->createPayment($payload);

        if (($payment['status'] ?? null) === 'error') {
            return response()->json([
                'success' => false,
                'payment' => $payment,
            ], 502);
        }

        DB::table('payments')->updateOrInsert(
            ['reference_no' => $payment['reference'] ?? $payload['external_id']],
            [
                'event_id' => $data['event_id'],
                'user_id' => Auth::id(),
                'amount' => $data['amount'],
                'status' => 'pending',
                'verified_at' => null,
                'created_at' => now(),
            ]
        );

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
        $paymongoSignature = $request->header('Paymongo-Signature');
        $secret = config('services.gcash.webhook_secret');
        $paymongoSecret = config('services.paymongo.webhook_secret');

        if ($secret && $signature) {
            $expected = hash_hmac('sha256', $request->getContent(), $secret);
            if (! hash_equals($expected, $signature)) {
                return response()->json(['success' => false], 403);
            }
        }

        if ($paymongoSecret) {
            if (! $paymongoSignature) {
                return response()->json(['success' => false], 403);
            }

            $parts = collect(explode(',', $paymongoSignature))
                ->mapWithKeys(function (string $part) {
                    [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);

                    return [$key => $value];
                });
            $timestamp = $parts->get('t');
            $testSignature = $parts->get('te');
            $liveSignature = $parts->get('li');
            $expected = $timestamp
                ? hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $paymongoSecret)
                : null;

            if (! $expected || (! $testSignature && ! $liveSignature) || (! hash_equals($expected, (string) $testSignature) && ! hash_equals($expected, (string) $liveSignature))) {
                return response()->json(['success' => false], 403);
            }
        }

        $eventType = (string) data_get($payload, 'data.attributes.type', '');
        $resource = data_get($payload, 'data.attributes.data', []);
        $resourceAttributes = $resource['attributes'] ?? [];
        $status = strtolower((string) ($resourceAttributes['status'] ?? $payload['status'] ?? ''));
        $reference = (string) ($resourceAttributes['payment_intent_id'] ?? '');

        if ($reference === '' && ($resource['type'] ?? '') === 'payment_intent') {
            $reference = (string) ($resource['id'] ?? '');
        }

        if ($reference === '') {
            $reference = (string) ($payload['external_id'] ?? $payload['reference'] ?? '');
        }

        if ($reference !== '') {
            $paymentStatus = match (true) {
                $eventType === 'payment.paid', $eventType === 'payment_intent.succeeded', $status === 'paid', $status === 'succeeded' => 'verified',
                $eventType === 'payment.failed', $eventType === 'qrph.expired', in_array($status, ['failed', 'expired'], true) => $status === 'expired' ? 'expired' : 'failed',
                default => 'pending',
            };

            $payment = DB::table('payments')
                ->where('reference_no', $reference)
                ->first();

            if ($payment) {
                DB::table('payments')
                    ->where('payment_id', $payment->payment_id)
                    ->update([
                        'status' => $paymentStatus,
                        'verified_at' => $paymentStatus === 'verified' ? now() : null,
                    ]);

                DB::table('events')
                    ->where('event_id', $payment->event_id)
                    ->update(['payment_status' => $paymentStatus]);
            }
        }

        return response()->json(['success' => true]);
    }
}
