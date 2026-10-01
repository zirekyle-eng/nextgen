<?php

namespace App\Services;

use App\Models\Setting;

class WhatsappSettings
{
    private ?array $settings = null;

    public function enabled(): bool
    {
        return $this->getBool('whatsapp_enabled', (bool) config('whatsapp.enabled', false));
    }

    public function notifyCameraOff(): bool
    {
        return $this->getBool('whatsapp_notify_camera_off', (bool) config('whatsapp.notify_camera_off', true));
    }

    public function notifyLateRealtime(): bool
    {
        return $this->getBool('whatsapp_notify_late_realtime', (bool) config('whatsapp.notify_late_realtime', true));
    }

    public function notifyAfterClassPresent(): bool
    {
        return $this->getBool('whatsapp_notify_after_class_present', (bool) config('whatsapp.notify_after_class_present', true));
    }

    public function notifyAfterClassLate(): bool
    {
        return $this->getBool('whatsapp_notify_after_class_late', (bool) config('whatsapp.notify_after_class_late', true));
    }

    public function notifyAfterClassAbsent(): bool
    {
        return $this->getBool('whatsapp_notify_after_class_absent', (bool) config('whatsapp.notify_after_class_absent', true));
    }

    public function notifyDailyAbsent(): bool
    {
        return $this->getBool('whatsapp_notify_daily_absent', (bool) config('whatsapp.notify_daily_absent', true));
    }

    public function notifyTermReport(): bool
    {
        return $this->getBool('whatsapp_notify_term_report', (bool) config('whatsapp.notify_term_report', true));
    }

    private function getBool(string $key, bool $default): bool
    {
        $settings = $this->load();
        if (!array_key_exists($key, $settings)) {
            return $default;
        }

        $value = strtolower(trim((string) $settings[$key]));
        if ($value === '') {
            return $default;
        }

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private function load(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $keys = [
            'whatsapp_enabled',
            'whatsapp_notify_camera_off',
            'whatsapp_notify_late_realtime',
            'whatsapp_notify_after_class_present',
            'whatsapp_notify_after_class_late',
            'whatsapp_notify_after_class_absent',
            'whatsapp_notify_daily_absent',
            'whatsapp_notify_term_report',
        ];

        $this->settings = Setting::whereIn('type', $keys)->pluck('description', 'type')->toArray();

        return $this->settings;
    }
}

