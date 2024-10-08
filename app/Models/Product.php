<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'shop_products';

    protected $fillable = [
        'name',
        'price',
        'sku',
        'qty',
        'cost',
        'barcode',
        'security_stock',
        'old_price',
        'description',
        'slug',
        'is_visible',
        'published_at'
    ];

    protected $casts = [
        'featured' => 'boolean',
        'is_visible' => 'boolean',
        'backorder' => 'boolean',
        'requires_shipping' => 'boolean',
        'published_at' => 'date',
    ];

    public function brand(): BelongsTo{
        return $this->belongsTo(Brand::class, 'shop_brand_id');
    }

    public function categories():BelongsToMany{
        return $this->belongsToMany(Category::class, 'shop_category_product', 'shop_product_id', 'shop_category_id')->withTimestamps();
    }

    public function comments(): MorphMany{
        return $this->morphMany(Comment::class, 'commentable');
    }
}
