<?php

namespace App\Services;

use App\Models\BbgMeeting;
use App\User;
use Carbon\Carbon;

class BbbWhatsappMessageBuilder
{
    public function cameraOff(User $student, ?BbgMeeting $meeting, Carbon $since, int $minutes): string
    {
        $meetingName = $meeting?->meeting_name ?: 'the class';
        $time = $since->format('H:i');

        return "Dear Parent
        As part of our quality assurance; 
        Camera alert: {$student->name} has not turned on the camera for {$minutes} minutes in {$meetingName}. Last off time: {$time}.";
    }

    public function lateArrival(User $student, ?BbgMeeting $meeting, int $lateMinutes, ?Carbon $joinAt, ?Carbon $scheduledStart): string
    {
        $meetingName = $meeting?->meeting_name ?: 'the class';
        $joinTime = $joinAt ? $joinAt->format('H:i') : '-';
        $startTime = $scheduledStart ? $scheduledStart->format('H:i') : '-';

        return "Dear Parent
        As part of our quality assurance; 
        Late alert: {$student->name} was {$lateMinutes} minutes late for {$meetingName}. Start time {$startTime}, join time {$joinTime}.";
    }

    public function afterClass(User $student, ?BbgMeeting $meeting, string $status, int $lateMinutes, ?Carbon $scheduledStart): string
    {
        $meetingName = $meeting?->meeting_name ?: 'the class';
        $date = $scheduledStart ? $scheduledStart->format('Y-m-d') : now()->format('Y-m-d');

        if ($status === 'absent') {
            return "Dear Parent
        As part of our quality assurance; 
        After-class report: {$student->name} was absent for {$meetingName} on {$date}.";
        }

        if ($status === 'late') {
            return "Dear Parent
        As part of our quality assurance; 
        After-class report: {$student->name} was {$lateMinutes} minutes late for {$meetingName} on {$date}.";
        }

        return "Dear Parent
        As part of our quality assurance; 
        After-class report: {$student->name} was present for {$meetingName} on {$date}.";
    }

    public function dailyAbsence(User $student, string $date): string
    {
        return "Dear Parent
        As part of our quality assurance; 
        Daily absence alert: {$student->name} did not attend any class on {$date}.";
    }

    public function termReport(User $student, string $termKey, Carbon $from, Carbon $to): string
    {
        return "Dear Parent
        As part of our quality assurance; 
        End of {$termKey} report is ready for {$student->name} for {$from->format('Y-m-d')} to {$to->format('Y-m-d')}. You can review it in your account.";
    }
}
