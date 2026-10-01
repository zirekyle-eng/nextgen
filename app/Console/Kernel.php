<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ExtractLessonFileContent::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('bbb:send-camera-off-alerts')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('bbb:send-late-arrival-alerts')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('bbb:finalize-meeting-attendance')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('bbb:send-daily-absence-alerts')
            ->dailyAt(config('bigbluebutton.attendance_daily_absent_at', '20:00'))
            ->withoutOverlapping();

        $schedule->command('bbb:send-term-attendance-reports')
            ->dailyAt(config('bigbluebutton.attendance_term_report_at', '19:00'))
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
