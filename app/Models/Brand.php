<?php

namespace App\Models;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Brand extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;


    protected $table = 'shop_brands';

    protected $fillable = [
        'name',
        'slug',
        'website',
        'is_visible'
    ];
    
    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'addressable', 'addressables');
    }


    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'shop_brand_id');
    }
}
