<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    /** @use HasFactory<\Database\Factories\ImageFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'path',
    ];

    /**
     * Get the parent imageable model (e.g., Product, User, etc.).
     *
     * This defines a polymorphic relationship, allowing the Image model
     * to be associated with multiple other models using `imageable_id` and `imageable_type`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
