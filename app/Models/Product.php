<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'long_description',
        'price',
        'gradient',
        'rating',
        'reviews_count',
        'specs',
        'featured',
        'stock',
    ];

    protected $casts = [
        'specs' => 'array',
        'featured' => 'boolean',
        'price' => 'float',
        'rating' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

