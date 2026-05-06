<?php

namespace App\Services;

use App\Mail\ReservationCanceled;
use App\Mail\ReservationConfirmation;
use App\Mail\ReservationReminder;
use App\Mail\ReservationUpdated;
use App\Models\Reservation;
use App\Models\TableSeat;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ReservationService
{
    /**
     * Create a new reservation.
     *
     * @param  array  $data
     * @param  \App\Models\User  $user
     * @param  bool  $wantsReminder
     * @param  int  $reminderMinutes
     * @return \App\Models\Reservation
     * @throws \Exception
     */
    public function create(array $data, User $user, bool $wantsReminder = false, int $reminderMinutes = 10): Reservation
    {
        $this->checkUserRestrictions($user);

        $existingReservation = Reservation::where('user_id', $user->id)
            ->whereIn('status', ['ongoing', 'seated'])
            ->exists();

        if ($existingReservation) {
            throw new Exception('You already have an active reservation.');
        }

        return DB::transaction(function () use ($data, $user, $wantsReminder, $reminderMinutes) {
            $table = TableSeat::lockForUpdate()->find($data['table']);

            if (!$table || $table->status !== 'available') {
                throw new Exception('Table is not available.');
            }

            $verificationCode = strtoupper(Str::random(6));

            $reservation = Reservation::create([
                'user_id'           => $user->id,
                'table_id'          => $data['table'],
                'datetime'          => $data['datetime'],
                'info'              => $data['info'] ?? null,
                'verification_code' => $verificationCode,
                'status'            => 'ongoing',
            ]);

            $table->update(['status' => 'reserved']);

            Mail::to($user->email)->queue(new ReservationConfirmation($user, $verificationCode));

            if ($wantsReminder) {
                $sendAt = Carbon::parse($data['datetime'])->subMinutes($reminderMinutes);
                Mail::to($user->email)->later($sendAt, new ReservationReminder($reservation));
            }

            return $reservation;
        });
    }

    /**
     * Cancel a reservation.
     *
     * @param  \App\Models\Reservation  $reservation
     * @param  \App\Models\User|null  $user
     * @param  string|null  $reason
     * @return void
     * @throws \Exception
     */
    public function cancel(Reservation $reservation, ?User $user = null, ?string $reason = null): void
    {
        if ($user) {
            if ($user->isBanned()) {
                throw new Exception('Your account is banned. You cannot cancel the reservation.');
            }

            if ($user->isTimedOut()) {
                $timeout = $user->timeouts()->where('expires_at', '>', now())->first();
                if ($timeout) {
                    $expiresAt = Carbon::parse($timeout->expires_at)->diffForHumans();
                    throw new Exception("You are currently in timeout. Please try again after {$expiresAt}.");
                }
            }

            if ($reservation->user_id !== $user->id) {
                throw new Exception('You are not authorized to cancel this reservation.');
            }

            if ($reservation->status !== 'ongoing') {
                throw new Exception('Reservation is not ongoing.');
            }
        }

        DB::transaction(function () use ($reservation) {
            $reservation->update(['status' => 'canceled']);
            $reservation->table->update(['status' => 'available']);
        });

        if ($reason) {
            Mail::to($reservation->user->email)->queue(new ReservationCanceled($reservation, $reason));
        }
    }

    /**
     * Update a reservation.
     *
     * @param  \App\Models\Reservation  $reservation
     * @param  array  $data
     * @param  \App\Models\User|null  $user
     * @return void
     * @throws \Exception
     */
    public function update(Reservation $reservation, array $data, ?User $user = null): void
    {
        if ($user) {
            $this->checkUserRestrictions($user);

            if ($reservation->user_id !== $user->id) {
                throw new Exception('You are not authorized to update this reservation.');
            }

            if ($reservation->status !== 'ongoing') {
                throw new Exception('Reservation is not ongoing.');
            }

            $hasUpdated = DB::table('reservation_updates')
                ->where('reservation_id', $reservation->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($hasUpdated) {
                throw new Exception('You can only update your reservation once.');
            }
        }

        DB::transaction(function () use ($reservation, $data, $user) {
            if ($reservation->table_id != $data['table']) {
                $oldTable = TableSeat::lockForUpdate()->find($reservation->table_id);
                $newTable = TableSeat::lockForUpdate()->find($data['table']);

                if ($newTable->status !== 'available') {
                    throw new Exception('Selected table is no longer available.');
                }

                $oldTable->update(['status' => 'available']);
                $newTable->update(['status' => 'reserved']);
            }

            $reservation->update([
                'table_id' => $data['table'],
                'datetime' => $data['datetime'],
                'info'     => $data['info'] ?? null,
            ]);

            if ($user) {
                DB::table('reservation_updates')->insert([
                    'reservation_id' => $reservation->id,
                    'user_id'        => $user->id,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        });

        Mail::to($reservation->user->email)->queue(new ReservationUpdated($reservation));
    }

    /**
     * Check user restrictions.
     *
     * @param  \App\Models\User  $user
     * @return void
     * @throws \Exception
     */
    private function checkUserRestrictions(User $user): void
    {
        if ($user->isBanned()) {
            throw new Exception('Your account is banned.');
        }

        if ($user->isTimedOut()) {
            $timeout = $user->timeouts()->where('expires_at', '>', now())->first();
            if ($timeout) {
                $expiresAt = Carbon::parse($timeout->expires_at)->diffForHumans();
                throw new Exception("You are currently in timeout. Please try again after {$expiresAt}.");
            }
        }
    }
}
