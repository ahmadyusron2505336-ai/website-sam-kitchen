<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $merchant = $request->user()->merchant;
        $range = in_array($request->range, ['7', '30', '12']) ? $request->range : '7';

        $done = fn () => $merchant->orders()->where('status', 'selesai');

        $totalSales     = (int) $done()->sum('total');
        $totalOrders    = $merchant->orders()->count();
        $totalCustomers = $merchant->orders()->distinct('customer_name')->count('customer_name');

        [$labels, $values] = $this->chartData($done, $range);

        // Produk terlaris: persentase dari seluruh porsi yang terjual
        $scope = fn ($q) => $q->where('merchant_id', $merchant->id)->where('status', 'selesai');

        $allSold = OrderItem::whereHas('order', $scope)->sum('qty');
        $bestSellers = OrderItem::select('product_name', DB::raw('SUM(qty) as sold'))
            ->whereHas('order', $scope)
            ->groupBy('product_name')->orderByDesc('sold')->limit(5)->get()
            ->each(fn ($s) => $s->percent = $allSold ? (int) round($s->sold / $allSold * 100) : 0);

        $latestOrders = $merchant->orders()->with('items')->latest()->limit(7)->get();

        return view('merchant.dashboard', compact(
            'totalSales', 'totalOrders', 'totalCustomers',
            'labels', 'values', 'range', 'bestSellers', 'latestOrders'
        ));
    }

    private function chartData(callable $done, string $range): array
    {
        $labels = [];
        $values = [];

        if ($range === '12') {
            $start = now()->subMonths(11)->startOfMonth();
            $rows = $done()->where('created_at', '>=', $start)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as k, SUM(total) as t")
                ->groupBy('k')->pluck('t', 'k');

            foreach (CarbonPeriod::create($start, '1 month', now()->startOfMonth()) as $m) {
                $labels[] = $m->copy()->locale('id')->isoFormat('MMM YY');
                $values[] = (int) ($rows[$m->format('Y-m')] ?? 0);
            }
            return [$labels, $values];
        }

        $days  = (int) $range;
        $start = now()->subDays($days - 1)->startOfDay();
        $rows  = $done()->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as k, SUM(total) as t')
            ->groupBy('k')->pluck('t', 'k');

        foreach (CarbonPeriod::create($start, now()->startOfDay()) as $d) {
            $labels[] = $days === 7 ? $d->copy()->locale('id')->isoFormat('dddd') : $d->format('d/m');
            $values[] = (int) ($rows[$d->format('Y-m-d')] ?? 0);
        }
        return [$labels, $values];
    }
}