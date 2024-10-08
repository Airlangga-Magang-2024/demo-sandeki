<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table ='shop_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_visible'
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function children(): HasMany{
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent(): BelongsTo{
        return $this->belongsTo(Category::class, 'parent_id');
    }
    
    public function products(): BelongsToMany{
        return $this->belongsToMany(Product::class,'shop_category_product','shop_category_id', 'shop_product_id');
    }
}
