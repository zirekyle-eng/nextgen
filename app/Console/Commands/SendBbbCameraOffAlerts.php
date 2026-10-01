<?php

namespace App\Console\Commands;

use App\Mail\StudentCameraOffAlertMail;
use App\Models\BbbCameraMonitor;
use App\Models\BbgMeeting;
use App\Models\StudentRecord;
use App\Services\BbbWhatsappMessageBuilder;
use App\Services\WhatsappGateway;
use App\Services\WhatsappSettings;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBbbCameraOffAlerts extends Command
{
    protected $signature = 'bbb:send-camera-off-alerts';

    protected $description = 'Send parent alerts when student camera stays off in BBB for configured minutes';

    public function handle(
        WhatsappGateway $whatsappGateway,
        BbbWhatsappMessageBuilder $messageBuilder,
        WhatsappSettings $whatsappSettings
    )
    {
        if (!config('bigbluebutton.camera_alert_enabled', true)) {
            $this->line('BBB camera alerts are disabled by configuration.');
            return self::SUCCESS;
        }

        $minutes = max((int) config('bigbluebutton.camera_alert_after_minutes', 10), 0);
        $threshold = now()->subMinutes($minutes);
        $sent = 0;
        $checked = 0;

        BbbCameraMonitor::whereNotNull('camera_off_since')
            ->whereNull('alert_sent_at')
            ->where('camera_off_since', '<=', $threshold)
            ->orderBy('id')
            ->chunkById(100, function ($monitors) use (&$sent, &$checked, $minutes, $whatsappGateway, $messageBuilder, $whatsappSettings) {
                foreach ($monitors as $monitor) {
                    $checked++;

                    if ($this->sendAlertForMonitor($monitor, $minutes, $whatsappGateway, $messageBuilder, $whatsappSettings)) {
                        $sent++;
                    }
                }
            });

        $this->info("BBB camera alerts checked: {$checked}, sent: {$sent}");

        return self::SUCCESS;
    }

    private function sendAlertForMonitor(
        BbbCameraMonitor $monitor,
        int $minutes,
        WhatsappGateway $whatsappGateway,
        BbbWhatsappMessageBuilder $messageBuilder,
        WhatsappSettings $whatsappSettings
    ): bool
    {
        $student = User::find($monitor->student_user_id);
        if (!$student || $student->user_type !== 'student') {
            return false;
        }

        $studentRecord = StudentRecord::where('user_id', $student->id)->first();
        if (!$studentRecord || !$studentRecord->my_parent_id) {
            Log::warning('BBB camera alert skipped: student has no parent mapping', [
                'student_user_id' => $student->id,
                'meeting_id' => $monitor->meeting_id,
            ]);
            return false;
        }

        $parent = User::find($studentRecord->my_parent_id);
        if (!$parent) {
            Log::warning('BBB camera alert skipped: parent missing', [
                'student_user_id' => $student->id,
                'parent_id' => $studentRecord->my_parent_id,
                'meeting_id' => $monitor->meeting_id,
            ]);
            return false;
        }

        $meeting = BbgMeeting::where('meeting_id', $monitor->meeting_id)->first();
        $emailSent = false;
        $whatsappSent = false;

        if (!empty($parent->email)) {
            try {
                Mail::to($parent->email)->send(new StudentCameraOffAlertMail(
                    $student,
                    $parent,
                    $meeting,
                    $monitor->camera_off_since,
                    $minutes
                ));
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::error('BBB camera alert email failed', [
                    'student_user_id' => $student->id,
                    'parent_id' => $parent->id,
                    'meeting_id' => $monitor->meeting_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($whatsappSettings->enabled() && $whatsappSettings->notifyCameraOff()) {
            $parentPhone = $parent->phone ?: $parent->phone2;
            if ($parentPhone) {
                $whatsappSent = $whatsappGateway->sendMessage(
                    $parentPhone,
                    $messageBuilder->cameraOff($student, $meeting, $monitor->camera_off_since, $minutes),
                    ['type' => 'camera_off', 'student_id' => $student->id, 'meeting_id' => $monitor->meeting_id]
                );
            } else {
                Log::warning('BBB camera alert skipped: parent phone missing', [
                    'student_user_id' => $student->id,
                    'parent_id' => $parent->id,
                    'meeting_id' => $monitor->meeting_id,
                ]);
            }
        }

        if ($emailSent || $whatsappSent) {
            $monitor->alert_sent_at = now();
            $monitor->save();
            return true;
        }

        return false;
    }
}
