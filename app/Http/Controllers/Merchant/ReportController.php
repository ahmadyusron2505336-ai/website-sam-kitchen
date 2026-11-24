<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\{Order, OrderItem};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function range(Request $request): array
    {
        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date']]);

        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->startOfMonth();
        $to   = $request->filled('to')   ? Carbon::parse($request->to)->endOfDay()     : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }
        return [$from, $to];
    }

    private function dailyQuery($merchant, $from, $to)
    {
        return Order::query()
            ->leftJoin('users', 'users.id', '=', 'orders.handled_by')
            ->where('orders.merchant_id', $merchant->id)
            ->where('orders.status', 'selesai')
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw("DATE(orders.created_at) as tanggal,
                         COUNT(*) as jumlah,
                         SUM(orders.total) as pendapatan,
                         GROUP_CONCAT(DISTINCT users.name SEPARATOR ', ') as admin")
            ->groupBy('tanggal')->orderByDesc('tanggal');
    }

    public function index(Request $request)
    {
        $merchant = $request->user()->merchant;
        [$from, $to] = $this->range($request);

        $base = fn () => $merchant->orders()->where('status', 'selesai')
            ->whereBetween('created_at', [$from, $to]);

        $revenue = (int) $base()->sum('total');
        $count   = $base()->count();
        $days    = max(1, (int) $from->diffInDays($to) + 1);
        $perDay  = round($count / $days);

        // Pembanding: periode sebelumnya dengan jumlah hari yang sama
        $prevFrom = $from->copy()->subDays($days);
        $prevTo   = $from->copy()->subSecond();
        $prev = (int) $merchant->orders()->where('status', 'selesai')
            ->whereBetween('created_at', [$prevFrom, $prevTo])->sum('total');
        $growth = $prev > 0 ? (int) round(($revenue - $prev) / $prev * 100) : null;

        $top = OrderItem::select('product_name', DB::raw('SUM(qty) as sold'))
            ->whereHas('order', fn ($q) => $q->where('merchant_id', $merchant->id)
                ->where('status', 'selesai')->whereBetween('created_at', [$from, $to]))
            ->groupBy('product_name')->orderByDesc('sold')->first();

        $daily = $this->dailyQuery($merchant, $from, $to)->get();

        return view('merchant.reports.index', compact(
            'from', 'to', 'revenue', 'count', 'perDay', 'growth', 'top', 'daily'
        ));
    }

    public function show(Request $request, string $date)
    {
        $day = Carbon::createFromFormat('Y-m-d', $date);

        $orders = $request->user()->merchant->orders()->with(['items', 'handler'])
            ->where('status', 'selesai')->whereDate('created_at', $day)
            ->latest()->get();

        return view('merchant.reports.show', compact('day', 'orders'));
    }

    public function export(Request $request)
    {
        $merchant = $request->user()->merchant;
        [$from, $to] = $this->range($request);
        $rows = $this->dailyQuery($merchant, $from, $to)->get();

        $filename = 'laporan-penjualan-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            fputcsv($out, ['Tanggal', 'Jumlah Transaksi', 'Total Pendapatan', 'Admin Penanggung Jawab'], ';');
            foreach ($rows as $r) {
                fputcsv($out, [$r->tanggal, $r->jumlah, $r->pendapatan, $r->admin ?: '-'], ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}