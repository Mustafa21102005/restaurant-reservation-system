<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableSeat extends Model
{
    /** @use HasFactory<\Database\Factories\TableSeatFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'capacity',
        'status'
    ];

    /**
     * Get the reservation associated with the table seat.
     *
     * This defines a one-to-one relationship between the TableSeat model
     * and the Reservation model. Each table seat can have at most one
     * reservation associated with it.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reservations()
    {
        return $this->hasOne(Reservation::class, 'table_id');
    }
}
