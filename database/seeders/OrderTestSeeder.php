<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Shop\Order;

class OrderTestSeeder extends Seeder
{
    public function run()
    {
        $order = new Order();
        $order->number = 'ORweq12312';
        $order->total_price = 101200;
        $order->currency = 'mxn';
        $order->save();
    }
}
