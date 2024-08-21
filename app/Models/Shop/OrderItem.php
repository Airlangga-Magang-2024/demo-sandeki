<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'shop_order_items';

    protected $fillable =[
        'shop_product_id',
        'qty',
        'unit_price'
    ];
}
