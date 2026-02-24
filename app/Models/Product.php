<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'type'
    ];

    /**
     * Get the category that this product belongs to.
     *
     * This defines an inverse one-to-many relationship between the Product model
     * and the Category model. Each product is associated with a single category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Define a one-to-one polymorphic relationship.
     *
     * This method establishes a polymorphic relationship between the Product model
     * and the Image model. It allows the Product model to be associated with a single
     * Image instance, where the 'imageable' type and ID are used to determine the
     * relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function imageable()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
