<?php

namespace App\Models\Shop;

use App\Models\Address;
use App\Models\Comment;
use App\Models\Shop\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'shop_customers';

    protected $fillable = [
        'name',
        'email',
        'birthday',
        'phone'
    ];

    protected $casts = [
        'birthday' => 'date',
    ];

    public function addresses(): MorphToMany{
        return $this->morphToMany(Address::class, 'addressable');
    }

    public function comments(): HasMany{
        return $this->hasMany(Comment::class);
    }

    public function payments(): HasManyThrough{
        return $this->hasManyThrough(Payment::class, Order::class, 'shop_customer_id');
    }

}
