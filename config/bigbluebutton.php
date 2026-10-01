<?php

return [
    'server_url' => env('BIGBLUEBUTTON_SERVER_URL'),
    'secret_key' => env('BIGBLUEBUTTON_SECRET_KEY'),
    'bbb_server_base_url' => env('BBB_SERVER_BASE_URL', 'https://viva-zoom.fame-uk.net/bigbluebutton/'),
    'bbb_secret' => env('BBB_SECRET', '4rr2saJkW8E5ofQTq0P3mMHLcHsrnWvBjnX78nCgY'),
    'webhook_token' => env('BBB_WEBHOOK_TOKEN', ''),
    'camera_alert_after_minutes' => env('BBB_CAMERA_ALERT_AFTER_MINUTES', 10),
    'camera_alert_enabled' => env('BBB_CAMERA_ALERT_ENABLED', true),
    'attendance_late_minutes' => env('BBB_ATTENDANCE_LATE_MINUTES', 5),
    'attendance_notify_present_after_class' => env('BBB_NOTIFY_PRESENT_AFTER_CLASS', true),
    'attendance_notify_late_realtime' => env('BBB_NOTIFY_LATE_REALTIME', true),
    'attendance_notify_late_after_class' => env('BBB_NOTIFY_LATE_AFTER_CLASS', true),
    'attendance_notify_absent_after_class' => env('BBB_NOTIFY_ABSENT_AFTER_CLASS', true),
    'attendance_notify_daily_absent' => env('BBB_NOTIFY_DAILY_ABSENT', true),
    'attendance_report_send_term_end' => env('BBB_SEND_TERM_REPORTS', true),
    'attendance_report_recipient' => env('BBB_TERM_REPORT_RECIPIENT', 'parents'),
    'attendance_after_class_delay_minutes' => env('BBB_AFTER_CLASS_DELAY_MINUTES', 5),
    'attendance_daily_absent_at' => env('BBB_DAILY_ABSENT_AT', '20:00'),
    'attendance_term_report_at' => env('BBB_TERM_REPORT_AT', '19:00'),
];
