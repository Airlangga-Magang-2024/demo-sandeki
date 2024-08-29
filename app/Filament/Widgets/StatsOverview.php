<?php

namespace App\Filament\Widgets;

use App\Models\Shop\Customer;
use Carbon\Carbon;
use App\Models\Shop\Order;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null ? Carbon::parse($this->filters['startDate']) : now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now();

        $previousStartDate = $startDate->copy()->subDays($startDate->diffInDays($endDate));
        $previousEndDate = $startDate->copy()->subDay();

        $revenue = Order::whereBetween('created_at', [$startDate, $endDate])->sum('total_price');
        $newCustomer = Customer::whereBetween('created_at', [$startDate, $endDate])->count();
        $newOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();

        $previousRevenue = Order::whereBetween('created_at', [$previousStartDate, $previousEndDate])->sum('total_price');
        $previousNewCustomer = Customer::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $previousNewOrders = Order::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();

        $revenueChange = $this->calculatePercentageChange($previousRevenue, $revenue);
        $customerChange = $this->calculatePercentageChange($previousNewCustomer, $newCustomer);
        $orderChange = $this->calculatePercentageChange($previousNewOrders, $newOrders);

        $formatNumber = function(int $number): string {
            if ($number < 1000) {
                return number_format($number, 0);
            }
            if ($number < 1000000) {
                return number_format($number / 1000, 2) . 'k';
            }
            return number_format($number / 1000000, 2) . 'm';
        };

        $revenueDescription = $revenueChange > 0 ? "{$revenueChange}% increase" : "{$revenueChange}% decrease";
        $revenueIcon = $revenueChange > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $revenueColor = $revenueChange > 0 ? 'success' : 'danger';

        $customerDescription = $customerChange > 0 ? "{$customerChange}% increase" : "{$customerChange}% decrease";
        $customerIcon = $customerChange > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $customerColor = $customerChange > 0 ? 'success' : 'danger';

        $orderDescription = $orderChange > 0 ? "{$orderChange}% increase" : "{$orderChange}% decrease";
        $orderIcon = $orderChange > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $orderColor = $orderChange > 0 ? 'success' : 'danger';

        return [
            Stat::make('Revenue', 'Rp ' . number_format($revenue, 0, ',', '.'))
                ->description($revenueDescription)
                ->descriptionIcon($revenueIcon)
                ->chart($this->getRevenueChart($startDate, $endDate))
                ->color($revenueColor),

            Stat::make('New Customers', $formatNumber($newCustomer))
                ->description($customerDescription)
                ->descriptionIcon($customerIcon)
                ->chart($this->getNewCustomersChart($startDate, $endDate))
                ->color($customerColor),

            Stat::make('New Orders', $formatNumber($newOrders))
                ->description($orderDescription)
                ->descriptionIcon($orderIcon)
                ->chart($this->getNewOrdersChart($startDate, $endDate))
                ->color($orderColor),
        ];
    }

    private function calculatePercentageChange($previousValue, $currentValue): float
    {
        if ($previousValue == 0) {
            return $currentValue > 0 ? 100 : 0;
        }

        return round((($currentValue - $previousValue) / $previousValue) * 100, 2);
    }

    protected function getRevenueChart($startDate, $endDate): array
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DAY(created_at) as day, SUM(total_price) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total')
            ->toArray();
    }

    protected function getNewCustomersChart($startDate, $endDate): array
    {
        return Customer::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DAY(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count')
            ->toArray();
    }

    protected function getNewOrdersChart($startDate, $endDate): array
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DAY(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count')
            ->toArray();
    }
}
