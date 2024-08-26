<?php

namespace App\Models\Shop;

use App\Enums\OrderStatus;
use App\Models\Shop\Customer;
use App\Models\Shop\OrderItem;
use App\Models\Shop\OrderAddress;
use App\Models\Shop\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'shop_orders';

    protected $fillable = [
        'number',
        'total_price',
        'status',
        'currency',
        'shipping_price',
        'shipping_method',
        'notes',
    ];

    protected static function booted(){
        static::saving(function ($order){
            $order->total_price = $order->calcTotalPrice();
            $order->shipping_price = $order->calcShipPrice();
        });
    }

    public function calcTotalPrice(){
        return $this->items->sum(fn ($state) => $state->unit_price * $state->qty);
    }

    public function calcShipPrice(){
        return 10000;
    }

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function address(): MorphOne{
        return $this->morphOne(OrderAddress::class, 'addressable');
    }

    public function customer(): BelongsTo{
        return $this->belongsTo(Customer::class, 'shop_customer_id');
    }

    public function items(): HasMany{
        return $this->hasMany(OrderItem::class, 'shop_order_id');
    }

    public function payments(): HasMany{
        return $this->hasMany(Payment::class);
    }

}
