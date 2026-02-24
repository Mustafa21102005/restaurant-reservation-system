<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ban extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'reason',
        'banned_by',
    ];

    /**
     * Get the user that has a ban.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
