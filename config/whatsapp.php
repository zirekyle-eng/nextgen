<?php

return [
    'enabled' => env('WHATSAPP_ENABLED', false),
    'gateway_url' => env('WHATSAPP_GATEWAY_URL', 'http://127.0.0.1:3020'),
    'gateway_token' => env('WHATSAPP_GATEWAY_TOKEN', ''),
    'timeout_seconds' => env('WHATSAPP_TIMEOUT_SECONDS', 10),
    'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '+972'),
    'notify_camera_off' => env('WHATSAPP_NOTIFY_CAMERA_OFF', true),
    'notify_late_realtime' => env('WHATSAPP_NOTIFY_LATE_REALTIME', true),
    'notify_after_class_present' => env('WHATSAPP_NOTIFY_PRESENT_AFTER_CLASS', true),
    'notify_after_class_late' => env('WHATSAPP_NOTIFY_LATE_AFTER_CLASS', true),
    'notify_after_class_absent' => env('WHATSAPP_NOTIFY_ABSENT_AFTER_CLASS', true),
    'notify_daily_absent' => env('WHATSAPP_NOTIFY_DAILY_ABSENT', true),
    'notify_term_report' => env('WHATSAPP_NOTIFY_TERM_REPORT', true),
];

