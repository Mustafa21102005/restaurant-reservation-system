<?php

namespace App\Console\Commands;

use App\Mail\NoShowNotification;
use App\Mail\ReservationWarningNotification;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MarkNoShowReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-no-show-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark reservations as no_show if time has passed 10 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Send warning emails only once for reservations within the next 5 minutes
        $warningTime = $now->copy()->addMinutes(5);

        $warnReservations = Reservation::with('user')
            ->where('status', 'ongoing')
            ->whereBetween('datetime', [$now, $warningTime])
            ->whereNull('warning_sent_at')
            ->get();

        Log::info("Found " . $warnReservations->count() . " reservations needing warning.");

        foreach ($warnReservations as $reservation) {
            try {
                if ($reservation->user && $reservation->user->email) {
                    Log::info("Sending warning to: {$reservation->user->email}");

                    Mail::to($reservation->user->email)
                        ->queue(new ReservationWarningNotification($reservation));

                    $reservation->warning_sent_at = now();
                    $reservation->save();
                }
            } catch (Throwable $e) {
                Log::error("Failed to send warning for Reservation ID {$reservation->id}: " . $e->getMessage());
            }
        }

        // Mark reservations as no_show if the time has passed by more than 10 minutes
        $lateReservations = Reservation::where('status', 'ongoing')
            ->where('datetime', '<=', $now->copy()->subMinutes(10))
            ->get();

        foreach ($lateReservations as $reservation) {
            try {
                $reservation->update(['status' => 'no_show']);

                if ($reservation->table) {
                    $reservation->table->update(['status' => 'available']);
                }

                if ($reservation->user && $reservation->user->email) {
                    Mail::to($reservation->user->email)
                        ->queue(new NoShowNotification($reservation));
                } else {
                    Log::warning("No-show email skipped: Reservation ID {$reservation->id} has no user.");
                }
            } catch (Throwable $e) {
                Log::error("Error processing late reservation ID {$reservation->id}: " . $e->getMessage());
            }
        }

        $this->info('Sent warnings and marked late reservations as no_show.');
    }
}
