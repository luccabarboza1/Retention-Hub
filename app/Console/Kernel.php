<?php

namespace App\Console;

use App\Models\WebhookDispatchLog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Purge failed_jobs older than 30 days
        $schedule->command('queue:prune-failed --hours=720')->weekly();

        // Purge webhook_dispatch_logs older than 90 days
        $schedule->call(function () {
            WebhookDispatchLog::where('created_at', '<', now()->subDays(90))->delete();
        })->weekly()->name('prune-webhook-logs')->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
