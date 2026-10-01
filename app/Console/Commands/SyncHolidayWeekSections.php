<?php

namespace App\Console\Commands;

use App\Models\MoodleSyncLog;
use App\Models\SubjectWeek;
use App\Services\MoodleService;
use Illuminate\Console\Command;

class SyncHolidayWeekSections extends Command
{
    protected $signature = 'academic:sync-holiday-sections';

    protected $description = 'Sync Moodle section names for weeks blocked by academic holidays.';

    private const HOLIDAY_BLOCK_PREFIX = 'Holiday: ';

    public function handle(MoodleService $moodle): int
    {
        $synced = 0;
        $skipped = 0;
        $failed = 0;

        SubjectWeek::query()
            ->with('subject.my_class')
            ->where('is_blocked', true)
            ->where('block_note', 'like', self::HOLIDAY_BLOCK_PREFIX . '%')
            ->orderBy('id')
            ->chunkById(100, function ($weeks) use ($moodle, &$synced, &$skipped, &$failed) {
                foreach ($weeks as $week) {
                    $subject = $week->subject;
                    if (!$subject || empty($subject->moodle_course_id)) {
                        $skipped++;
                        continue;
                    }

                    $sectionName = $this->buildWeekSectionName($week);
                    $result = $moodle->syncCourseSectionName(
                        (int) $subject->moodle_course_id,
                        (int) $week->week_number,
                        $sectionName
                    );

                    $status = !empty($result['ok']) ? 'success' : (!empty($result['skipped']) ? 'skipped' : 'failed');
                    MoodleSyncLog::create([
                        'entity_type' => 'subject_week',
                        'entity_id' => (int) $week->id,
                        'action' => 'holiday_section_sync',
                        'status' => $status,
                        'moodle_course_id' => (int) $subject->moodle_course_id,
                        'response_payload' => json_encode([
                            'section_name' => $sectionName,
                            'result' => $result,
                        ], JSON_UNESCAPED_UNICODE),
                        'error_message' => $result['error'] ?? null,
                    ]);

                    if ($status === 'success') {
                        $synced++;
                    } elseif ($status === 'skipped') {
                        $skipped++;
                        $this->warn("Skipped week {$week->id}: " . (string) ($result['error'] ?? 'section sync is not configured'));
                    } else {
                        $failed++;
                        $this->error("Failed week {$week->id}: " . (string) ($result['error'] ?? 'unknown Moodle error'));
                    }
                }
            });

        $this->info("Holiday sections synced: {$synced}; skipped: {$skipped}; failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function buildWeekSectionName(SubjectWeek $week): string
    {
        $title = trim((string) $week->title);
        $sectionName = $title !== '' ? "Week {$week->week_number} - {$title}" : "Week {$week->week_number}";
        $holidayName = $this->extractHolidayNameFromBlockNote($week->block_note);

        return $holidayName !== '' ? $sectionName . ' - ' . $holidayName : $sectionName;
    }

    private function extractHolidayNameFromBlockNote(?string $note): string
    {
        $note = trim((string) $note);
        if (strpos($note, self::HOLIDAY_BLOCK_PREFIX) !== 0) {
            return '';
        }

        $holidayName = trim(substr($note, strlen(self::HOLIDAY_BLOCK_PREFIX)));
        $datePosition = strpos($holidayName, ' (');
        if ($datePosition !== false) {
            $holidayName = trim(substr($holidayName, 0, $datePosition));
        }

        return $holidayName;
    }
}
