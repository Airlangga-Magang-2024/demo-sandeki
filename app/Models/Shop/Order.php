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

    protected static function booted()
    {
        static::saving(function (Order $order) {
            $order->total_price = $order->calcTotalPrice();
            $order->shipping_price = $order->calcShipPrice();
        });
    }

    public function calcTotalPrice(): float
    {
        // return $this->items->sum(fn ($item) => $item->unit_price * $item->qty);
        return $this->items->sum(function ($item) {
            return $item->qty * $item->unit_price;
        });
    }

    public function calcShipPrice()
    {
        return 10000; // Adjust this logic as needed
    }


    public function getTotalPriceAttribute(): float
    {
        // return number_format(2, ',', '.'); // Format: ribuan titik, desimal koma
        return $this->items->sum(function ($item) {
            return $item->qty * $item->unit_price;
        });
    }
    // protected static function booted()
    // {
    //     static::saving(function (Order $order) {
    //                 $order->total_price = $order->calcTotalPrice();
    //                 $order->shipping_price = $order->calcShipPrice();
    //             });

    //     // static::saving(function (Order $order) {
    //         // $order = $this->order;
    //         // $order->total_price = $order->calcTotalPrice();
    //         // $order->save();
    //         // $order->shipping_price = $order->getShippingPriceAttribute();
    //     // });
    // }

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
