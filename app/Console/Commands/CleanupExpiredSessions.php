<?php

namespace App\Console\Commands;

use App\Services\TrackingService;
use Illuminate\Console\Command;

class CleanupExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tracking:cleanup';

    /**
     * The console command description.
     */
    protected $description = 'Mark expired tracking sessions and clean up stale data';

    /**
     * Execute the console command.
     */
    public function handle(TrackingService $trackingService): int
    {
        $count = $trackingService->cleanupExpiredSessions();

        $this->info("Marked {$count} session(s) as expired.");

        return self::SUCCESS;
    }
}
