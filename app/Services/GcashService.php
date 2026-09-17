<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GcashService
{
    public function createPayment(array $payload): array
    {
        $paymongoEnabled = config('services.paymongo.enabled', false);
        $paymongoKey = config('services.paymongo.secret_key');

        if ($paymongoEnabled && ! empty($paymongoKey)) {
            $amountInCentavos = (int) round(((float) ($payload['amount'] ?? 0)) * 100);
            $checkoutData = [
                'data' => [
                    'attributes' => [
                        'line_items' => [[
                            'amount' => $amountInCentavos,
                            'currency' => 'PHP',
                            'description' => $payload['description'] ?? 'EventIntel payment',
                            'quantity' => 1,
                            'name' => $payload['description'] ?? 'EventIntel service payment',
                        ]],
                        'payment_method_types' => ['card', 'gcash', 'paymaya'],
                        'success_url' => $payload['success_url'] ?? route('your.events', ['payment_status' => 'success']),
                        'cancel_url' => $payload['cancel_url'] ?? route('your.events', ['payment_status' => 'cancelled']),
                        'description' => $payload['description'] ?? 'EventIntel payment',
                        'reference_number' => $payload['external_id'] ?? 'eventintel-payment',
                    ],
                ],
            ];
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($paymongoKey . ':'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(config('services.paymongo.base_url') . '/checkout_sessions', $checkoutData);

            if ($response->successful()) {
                $data = $response->json();
                $session = $data['data']['attributes'] ?? [];

                return [
                    'status' => 'created',
                    'checkout_url' => $session['checkout_url'] ?? null,
                    'reference' => $data['data']['id'] ?? ($payload['external_id'] ?? 'paymongo-reference'),
                    'amount' => $payload['amount'] ?? 0,
                    'message' => 'PayMongo checkout created successfully.',
                    'qr_code' => null,
                ];
            }

            return [
                'status' => 'error',
                'checkout_url' => null,
                'reference' => $payload['external_id'] ?? 'paymongo-reference',
                'amount' => $payload['amount'] ?? 0,
                'message' => 'PayMongo payment request failed.',
                'qr_code' => null,
            ];
        }

        $enabled = config('services.gcash.enabled', false);
        $apiKey = config('services.gcash.api_key');

        if (! $enabled || empty($apiKey)) {
            return [
                'status' => 'sandbox',
                'checkout_url' => null,
                'reference' => $payload['external_id'] ?? 'sandbox-reference',
                'amount' => $payload['amount'] ?? 0,
                'message' => 'Online payment is not configured. Add PAYMONGO_ENABLED=true and PAYMONGO_SECRET_KEY to the environment.',
                'qr_code' => null,
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($apiKey . ':'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(config('services.gcash.base_url') . '/v2/invoices', [
            'external_id' => $payload['external_id'],
            'amount' => (float) ($payload['amount'] ?? 0),
            'description' => $payload['description'] ?? 'EventIntel payment',
            'currency' => $payload['currency'] ?? 'PHP',
            'metadata' => $payload['metadata'] ?? [],
        ]);

        if ($response->failed()) {
            return [
                'status' => 'error',
                'checkout_url' => null,
                'reference' => $payload['external_id'] ?? 'gcash-reference',
                'amount' => $payload['amount'] ?? 0,
                'message' => 'GCash payment request failed.',
                'qr_code' => null,
            ];
        }

        $data = $response->json();

        return [
            'status' => $data['status'] ?? 'created',
            'checkout_url' => $data['invoice_url'] ?? $data['payment_url'] ?? null,
            'reference' => $data['external_id'] ?? $payload['external_id'] ?? 'gcash-reference',
            'amount' => $data['amount'] ?? $payload['amount'] ?? 0,
            'message' => 'GCash payment created successfully.',
            'qr_code' => $data['qr_code'] ?? null,
        ];
    }

    public function verifyPayment(string $reference): array
    {
        if (! config('services.gcash.enabled', false) || empty(config('services.gcash.api_key'))) {
            return [
                'status' => 'sandbox',
                'verified' => true,
                'reference' => $reference,
                'message' => 'Sandbox verification accepted for local testing.',
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(config('services.gcash.api_key') . ':'),
            'Accept' => 'application/json',
        ])->get(config('services.gcash.base_url') . '/v2/invoices/' . $reference);

        if ($response->failed()) {
            return [
                'status' => 'failed',
                'verified' => false,
                'reference' => $reference,
                'message' => 'Unable to verify payment.',
            ];
        }

        $data = $response->json();
        $paid = ($data['status'] ?? '') === 'PAID' || ($data['status'] ?? '') === 'paid';

        return [
            'status' => $data['status'] ?? 'unknown',
            'verified' => $paid,
            'reference' => $reference,
            'message' => $paid ? 'Payment verified.' : 'Payment not yet verified.',
        ];
    }
}
