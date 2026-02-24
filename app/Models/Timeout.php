<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timeout extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'reason',
        'expires_at',
        'timeout_by',
    ];

    /**
     * Get the user that owns the timeout.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
