<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'table_id',
        'datetime',
        'info',
        'verification_code',
        'status'
    ];

    /**
     * Get the user that made the reservation.
     *
     * This defines an inverse one-to-many relationship where a reservation belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the table associated with the reservation.
     *
     * This defines an inverse one-to-many relationship where a reservation belongs to a table seat.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function table()
    {
        return $this->belongsTo(TableSeat::class, 'table_id');
    }
}
