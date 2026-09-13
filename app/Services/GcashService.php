<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GcashService
{
    public function createPayment(array $payload): array
    {
        $enabled = config('services.gcash.enabled', false);
        $apiKey = config('services.gcash.api_key');

        if (! $enabled || empty($apiKey)) {
            return [
                'status' => 'sandbox',
                'checkout_url' => null,
                'reference' => $payload['external_id'] ?? 'sandbox-reference',
                'amount' => $payload['amount'] ?? 0,
                'message' => 'GCash integration is not configured. Sandbox mode is active.',
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
