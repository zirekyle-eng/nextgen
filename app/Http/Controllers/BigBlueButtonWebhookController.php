<?php

namespace App\Http\Controllers;

use App\Models\BbbCameraMonitor;
use App\Models\BbbMeetingAttendance;
use App\Models\BbgMeeting;
use App\Services\BbbAttendanceService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BigBlueButtonWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        $eventPayload = $this->normalizeEventPayload($payload);
        $eventName = $this->extractEventName($payload, $eventPayload);
        $meetingId = $this->extractMeetingId($payload, $eventPayload);

        if ($eventName === '') {
            Log::warning('BBB webhook ignored: missing event name', ['payload' => $payload, 'event_payload' => $eventPayload]);
            return response()->json(['ok' => true, 'ignored' => 'missing_event']);
        }

        if ($meetingId === '') {
            Log::warning('BBB webhook ignored: missing meeting id', [
                'event' => $eventName,
                'payload' => $payload,
                'event_payload' => $eventPayload,
            ]);
            return response()->json(['ok' => true, 'ignored' => 'missing_meeting']);
        }

        $meeting = $this->findMeetingByExternalId($meetingId);
        $canonicalMeetingId = $meeting ? $meeting->meeting_id : $meetingId;

        if ($this->isMeetingEndedEvent($eventName)) {
            if ($meeting) {
                $meeting->status = 'ended';
                $meeting->ended_at = now();
                $meeting->save();
            }

            BbbCameraMonitor::where('meeting_id', $canonicalMeetingId)->update([
                'camera_off_since' => null,
                'last_event_name' => $eventName,
                'updated_at' => now(),
            ]);

            return response()->json(['ok' => true, 'processed' => 'meeting_ended']);
        }

        if (
            !$this->isCameraOffEvent($eventName)
            && !$this->isCameraOnEvent($eventName)
            && !$this->isUserLeftEvent($eventName)
            && !$this->isUserJoinedEvent($eventName)
        ) {
            return response()->json(['ok' => true, 'ignored' => 'event_not_tracked']);
        }

        $rawUserId = $this->extractUserId($payload, $eventPayload);
        $studentUserId = $this->normalizeUserId($rawUserId);

        if (!$studentUserId) {
            Log::warning('BBB webhook ignored: invalid user id', [
                'event' => $eventName,
                'meeting_id' => $meetingId,
                'raw_user_id' => $rawUserId,
            ]);
            return response()->json(['ok' => true, 'ignored' => 'invalid_user']);
        }

        $student = User::select(['id', 'user_type'])->find($studentUserId);
        if (!$student || $student->user_type !== 'student') {
            return response()->json(['ok' => true, 'ignored' => 'not_student']);
        }

        // Process only meetings created by this LMS.
        if (!$meeting) {
            return response()->json(['ok' => true, 'ignored' => 'unknown_meeting']);
        }

        if ($this->isCameraOffEvent($eventName) || $this->isCameraOnEvent($eventName) || $this->isUserLeftEvent($eventName)) {
            $monitor = BbbCameraMonitor::firstOrNew([
                'meeting_id' => $canonicalMeetingId,
                'student_user_id' => $studentUserId,
            ]);

            $monitor->last_event_name = $eventName;

            if ($this->isCameraOffEvent($eventName)) {
                if (!$monitor->camera_off_since) {
                    $monitor->camera_off_since = now();
                }
            } else {
                // Camera resumed or user left meeting; stop tracking current off-window.
                $monitor->camera_off_since = null;
            }

            $monitor->save();
        }

        if ($this->isUserJoinedEvent($eventName)) {
            $monitor = BbbCameraMonitor::firstOrNew([
                'meeting_id' => $canonicalMeetingId,
                'student_user_id' => $studentUserId,
            ]);
            if (!$monitor->camera_off_since) {
                $monitor->camera_off_since = now();
            }
            $monitor->last_event_name = $eventName;
            $monitor->save();

            $this->recordStudentAttendance($meeting, $studentUserId);
        }

        if ($this->isCameraOnEvent($eventName)) {
            $this->recordCameraOn($meeting, $studentUserId);
        }

        if ($this->isUserLeftEvent($eventName)) {
            $this->recordStudentLeft($meeting, $studentUserId);
        }

        return response()->json(['ok' => true, 'processed' => true]);
    }

    private function isAuthorized(Request $request): bool
    {
        $expectedToken = trim((string) config('bigbluebutton.webhook_token', ''));
        if ($expectedToken === '') {
            return true;
        }

        $incomingToken = trim((string) (
            $request->header('X-BBB-Webhook-Token')
            ?: $request->query('token')
            ?: $request->input('token')
        ));

        return $incomingToken !== '' && hash_equals($expectedToken, $incomingToken);
    }

    private function extractEventName(array $payload, array $eventPayload): string
    {
        $candidates = [
            data_get($payload, 'envelope.name'),
            data_get($payload, 'event'),
            data_get($payload, 'eventName'),
            data_get($payload, 'data.id'),
            data_get($payload, 'data.event'),
            data_get($eventPayload, 'data.id'),
            data_get($eventPayload, 'data.event'),
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '' && (substr($value, 0, 1) === '{' || substr($value, 0, 1) === '[')) {
                continue;
            }
            if ($value !== '') {
                return strtolower($value);
            }
        }

        return '';
    }

    private function extractMeetingId(array $payload, array $eventPayload): string
    {
        $candidates = [
            data_get($payload, 'data.meeting.external-meeting-id'),
            data_get($payload, 'data.meeting.id'),
            data_get($payload, 'data.attributes.meeting.external-meeting-id'),
            data_get($payload, 'data.attributes.meeting.id'),
            data_get($payload, 'core.header.meetingId'),
            data_get($payload, 'envelope.routing.meetingId'),
            data_get($payload, 'meeting_id'),
            data_get($payload, 'meetingId'),
            data_get($eventPayload, 'data.meeting.external-meeting-id'),
            data_get($eventPayload, 'data.meeting.id'),
            data_get($eventPayload, 'data.attributes.meeting.external-meeting-id'),
            data_get($eventPayload, 'data.attributes.meeting.id'),
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function extractUserId(array $payload, array $eventPayload): string
    {
        $candidates = [
            data_get($payload, 'data.user.external-user-id'),
            data_get($payload, 'data.user.userId'),
            data_get($payload, 'data.user.id'),
            data_get($payload, 'data.attributes.user.external-user-id'),
            data_get($payload, 'data.attributes.user.userId'),
            data_get($payload, 'data.attributes.user.id'),
            data_get($payload, 'core.header.userId'),
            data_get($payload, 'user_id'),
            data_get($payload, 'userId'),
            data_get($eventPayload, 'data.user.external-user-id'),
            data_get($eventPayload, 'data.user.userId'),
            data_get($eventPayload, 'data.user.id'),
            data_get($eventPayload, 'data.attributes.user.external-user-id'),
            data_get($eventPayload, 'data.attributes.user.userId'),
            data_get($eventPayload, 'data.attributes.user.id'),
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function normalizeEventPayload(array $payload): array
    {
        $event = $payload['event'] ?? null;

        if (is_string($event)) {
            $decoded = json_decode($event, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                if ($this->isList($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
                    return $decoded[0];
                }
                return $decoded;
            }
        }

        if (is_array($event)) {
            if ($this->isList($event) && isset($event[0]) && is_array($event[0])) {
                return $event[0];
            }
            return $event;
        }

        return $payload;
    }

    private function isList(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    private function normalizeUserId(string $rawUserId): ?int
    {
        if ($rawUserId === '') {
            return null;
        }

        if (ctype_digit($rawUserId)) {
            return (int) $rawUserId;
        }

        if (preg_match('/(\d+)/', $rawUserId, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function isCameraOffEvent(string $eventName): bool
    {
        return in_array($eventName, [
            'user-cam-broadcast-end',
            'user-cam-broadcast-stop',
            'user-cam-end',
        ], true);
    }

    private function isCameraOnEvent(string $eventName): bool
    {
        return in_array($eventName, [
            'user-cam-broadcast-start',
            'user-cam-start',
        ], true);
    }

    private function isUserLeftEvent(string $eventName): bool
    {
        return in_array($eventName, [
            'user-left',
            'user-disconnected',
        ], true);
    }

    private function isUserJoinedEvent(string $eventName): bool
    {
        return in_array($eventName, [
            'user-joined',
            'user-join',
        ], true);
    }

    private function isMeetingEndedEvent(string $eventName): bool
    {
        return in_array($eventName, [
            'meeting-ended',
            'meeting-ended-event',
        ], true);
    }

    private function recordStudentAttendance(BbgMeeting $meeting, int $studentUserId): void
    {
        if (!$meeting->started_at) {
            $meeting->started_at = now();
            $meeting->status = $meeting->status ?: 'started';
            $meeting->save();
        }

        $attendance = BbbMeetingAttendance::firstOrNew([
            'meeting_id' => $meeting->meeting_id,
            'student_user_id' => $studentUserId,
        ]);

        if (!$attendance->join_at) {
            $attendance->join_at = now();
        }

        if (!$attendance->scheduled_start_at || !$attendance->scheduled_end_at) {
            [$scheduledStart, $scheduledEnd] = $this->resolveMeetingSchedule($meeting);
            $attendance->scheduled_start_at = $scheduledStart;
            $attendance->scheduled_end_at = $scheduledEnd;
        }

        $lateMinutes = 0;
        if ($attendance->scheduled_start_at) {
            $lateMinutes = max((int) $attendance->join_at->diffInMinutes($attendance->scheduled_start_at, false), 0);
        }

        $attendance->late_minutes = $lateMinutes;
        $attendance->status = $lateMinutes >= (int) config('bigbluebutton.attendance_late_minutes', 5) ? 'late' : 'present';
        $attendance->save();
    }

    private function recordStudentLeft(BbgMeeting $meeting, int $studentUserId): void
    {
        $attendance = BbbMeetingAttendance::where('meeting_id', $meeting->meeting_id)
            ->where('student_user_id', $studentUserId)
            ->first();

        if (!$attendance) {
            return;
        }

        if (!$attendance->left_at) {
            $attendance->left_at = now();
            $attendance->save();
        }
    }

    private function recordCameraOn(BbgMeeting $meeting, int $studentUserId): void
    {
        $attendance = BbbMeetingAttendance::firstOrNew([
            'meeting_id' => $meeting->meeting_id,
            'student_user_id' => $studentUserId,
        ]);

        if (!$attendance->join_at) {
            $attendance->join_at = now();
        }

        $attendance->camera_on_count = (int) ($attendance->camera_on_count ?? 0) + 1;
        if (!$attendance->first_camera_on_at) {
            $attendance->first_camera_on_at = now();
        }
        $attendance->last_camera_on_at = now();

        $attendance->save();
    }

    private function findMeetingByExternalId(string $meetingId): ?BbgMeeting
    {
        $normalized = strtolower($meetingId);
        return BbgMeeting::whereRaw('LOWER(meeting_id) = ?', [$normalized])->first();
    }

    private function resolveMeetingSchedule(BbgMeeting $meeting): array
    {
        return app(BbbAttendanceService::class)->resolveMeetingSchedule($meeting);
    }
}
