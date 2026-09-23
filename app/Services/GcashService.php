<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GcashService
{
    public function createPayment(array $payload): array
    {
        $paymongoEnabled = config('services.paymongo.enabled', false);
        $paymongoKey = config('services.paymongo.secret_key');
        $paymongoPublicKey = config('services.paymongo.public_key');

        if ($paymongoEnabled && ! empty($paymongoKey)) {
            if (empty($paymongoPublicKey)) {
                return [
                    'status' => 'error',
                    'reference' => $payload['external_id'] ?? 'paymongo-reference',
                    'amount' => $payload['amount'] ?? 0,
                    'message' => 'PayMongo public key is not configured. Add PAYMONGO_PUBLIC_KEY to the environment.',
                ];
            }
            $amountInCentavos = (int) round(((float) ($payload['amount'] ?? 0)) * 100);
            $paymentIntentData = [
                'data' => [
                    'attributes' => [
                        'amount' => $amountInCentavos,
                        'currency' => $payload['currency'] ?? 'PHP',
                        'payment_method_allowed' => [$payload['payment_method'] ?? 'gcash'],
                        'description' => $payload['description'] ?? 'EventIntel payment',
                        'metadata' => $payload['metadata'] ?? [],
                    ],
                ],
            ];
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($paymongoKey . ':'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(config('services.paymongo.base_url') . '/payment_intents', $paymentIntentData);

            if ($response->successful()) {
                $data = $response->json();
                $intent = $data['data'] ?? [];
                $attributes = $intent['attributes'] ?? [];

                return [
                    'status' => 'created',
                    'payment_intent_id' => $intent['id'] ?? null,
                    'client_key' => $attributes['client_key'] ?? null,
                    'reference' => $intent['id'] ?? ($payload['external_id'] ?? 'paymongo-reference'),
                    'amount' => $payload['amount'] ?? 0,
                    'message' => 'PayMongo payment authorization created successfully.',
                    'payment_method' => $payload['payment_method'] ?? 'gcash',
                    'public_key' => $paymongoPublicKey,
                ];
            }

            $errors = $response->json('errors', []);
            Log::warning('PayMongo QR Ph Payment Intent creation failed.', [
                'status' => $response->status(),
                'errors' => $errors,
            ]);

            return [
                'status' => 'error',
                'reference' => $payload['external_id'] ?? 'paymongo-reference',
                'amount' => $payload['amount'] ?? 0,
                'message' => data_get($errors, '0.detail', 'PayMongo QR Ph payment request failed.'),
                'errors' => $errors,
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
        $paymongoKey = config('services.paymongo.secret_key');

        if (config('services.paymongo.enabled', false) && ! empty($paymongoKey) && str_starts_with($reference, 'pi_')) {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($paymongoKey . ':'),
                'Accept' => 'application/json',
            ])->get(config('services.paymongo.base_url') . '/payment_intents/' . $reference);

            if ($response->failed()) {
                return [
                    'status' => 'failed',
                    'verified' => false,
                    'reference' => $reference,
                    'message' => 'Unable to verify PayMongo payment.',
                ];
            }

            $status = strtolower((string) $response->json('data.attributes.status', 'unknown'));
            $verified = $status === 'succeeded';

            return [
                'status' => $status,
                'verified' => $verified,
                'reference' => $reference,
                'message' => $verified ? 'Payment verified.' : 'Payment not yet verified.',
            ];
        }

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
