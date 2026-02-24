<?php

namespace App\Console\Commands;

use App\Mail\TimeoutExpiredMail;
use App\Models\Timeout;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SoftDeleteExpiredTimeouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timeouts:soft-delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft delete timeouts that have passed their expiration time';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Fetch the expired timeouts
        $expiredTimeouts = Timeout::where('expires_at', '<=', now()->setTimezone('Asia/Riyadh'))
            ->whereNull('deleted_at')
            ->get();

        // Log how many expired timeouts are found
        $this->info("Found {$expiredTimeouts->count()} expired timeouts.");

        // If there are no expired timeouts, return early
        if ($expiredTimeouts->isEmpty()) {
            $this->info("No expired timeouts found.");
            return;
        }

        // Loop through each expired timeout and soft delete it
        foreach ($expiredTimeouts as $timeout) {
            $this->info("Soft deleting timeout ID: {$timeout->id}, expires at: {$timeout->expires_at}");

            $timeout->delete();

            // Queue the email to the user
            Mail::to($timeout->user->email)->queue(new TimeoutExpiredMail($timeout));
            $this->info("Email queued for user ID: {$timeout->user->id}");
        }
    }
}
