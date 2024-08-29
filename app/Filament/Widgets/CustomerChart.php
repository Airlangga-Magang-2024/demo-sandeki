<?php

namespace App\Filament\Widgets;

use Flowframe\Trend\Trend;
use App\Models\Shop\Customer;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\TrendValue;

class CustomerChart extends ChartWidget
{
    protected static ?string $heading = 'Total Customer';

    protected static ?int $sort = 2;
    
    protected function getData(): array
    {
        $data = Trend::model(Customer::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Total Customer',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'fill' => 'start',
                ],               
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

}
