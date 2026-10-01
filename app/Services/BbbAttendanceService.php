<?php

namespace App\Services;

use App\Models\BbgMeeting;
use App\Models\StudentRecord;
use App\Models\TimeSlot;
use App\Models\TimeTable;
use App\User;
use Carbon\Carbon;

class BbbAttendanceService
{
    public function resolveParent(User $student): ?User
    {
        $record = StudentRecord::where('user_id', $student->id)->first();
        if (!$record || !$record->my_parent_id) {
            return null;
        }

        return User::find($record->my_parent_id);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function resolveMeetingSchedule(BbgMeeting $meeting): array
    {
        $fallbackStart = $meeting->started_at ?: $meeting->created_at ?: now();
        $fallbackEnd = $meeting->ended_at ?: $fallbackStart->copy()->addHour();

        if (!$meeting->ttr_id) {
            return [$fallbackStart, $fallbackEnd];
        }

        $timeTable = null;
        if ($meeting->tt_id) {
            $timeTable = TimeTable::find($meeting->tt_id);
        }

        if (!$timeTable) {
            return [$fallbackStart, $fallbackEnd];
        }

        $timeSlot = TimeSlot::find($timeTable->ts_id);
        if (!$timeSlot) {
            return [$fallbackStart, $fallbackEnd];
        }

        $date = ($meeting->started_at ?: $meeting->created_at ?: now())->toDateString();
        $start = Carbon::parse($date . ' ' . $timeSlot->time_from);
        $end = Carbon::parse($date . ' ' . $timeSlot->time_to);

        return [$start, $end];
    }
}
