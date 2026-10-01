<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappGateway
{
    public function sendMessage(?string $phone, string $message, array $meta = []): bool
    {
        if (!app(WhatsappSettings::class)->enabled()) {
            return false;
        }

        $to = $this->normalizePhone($phone);
        if (!$to) {
            return false;
        }

        $gatewayUrl = rtrim((string) config('whatsapp.gateway_url', ''), '/');
        if ($gatewayUrl === '') {
            return false;
        }

        $payload = [
            'to' => $to,
            'message' => $message,
            'meta' => $meta,
        ];

        try {
            $response = Http::timeout((int) config('whatsapp.timeout_seconds', 10))
                ->withHeaders([
                    'X-WhatsApp-Token' => (string) config('whatsapp.gateway_token', ''),
                ])
                ->post($gatewayUrl . '/send', $payload);

            if ($response->successful()) {
                return (bool) ($response->json('ok') ?? true);
            }

            Log::warning('WhatsApp gateway returned error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => $to,
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp gateway request failed', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);
        }

        return false;
    }

    private function normalizePhone(?string $phone): ?string
    {
        $raw = trim((string) $phone);
        if ($raw === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '') {
            return null;
        }

        if (strpos($raw, '+') === 0) {
            return '+' . $digits;
        }

        if (strpos($digits, '00') === 0) {
            return '+' . substr($digits, 2);
        }

        $default = trim((string) config('whatsapp.default_country_code', ''));
        if ($default !== '') {
            if ($default[0] !== '+') {
                $default = '+' . $default;
            }
            if (strpos($digits, '0') === 0) {
                $digits = ltrim($digits, '0');
            }
            return $default . $digits;
        }

        return $digits;
    }
}
