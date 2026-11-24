<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\{Commission, Order, Product};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    const COMMISSION_RATE = 10; // persen

    private function own(Request $r, Order $o): void
    {
        abort_if($o->merchant_id !== $r->user()->merchant->id, 403);
    }

    public function index(Request $request)
    {
        $merchant = $request->user()->merchant;
        $status = $request->get('status', 'semua');

        $orders = $merchant->orders()->with('items')
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->latest()->get();

        $counts = $merchant->orders()->selectRaw('status, count(*) c')
            ->groupBy('status')->pluck('c', 'status');
        $counts = $counts->put('semua', $counts->sum());

        return view('merchant.orders.index', compact('orders', 'status', 'counts'));
    }

    public function feed(Request $request)
    {
        $merchant = $request->user()->merchant;

        return response()->json([
            'waiting' => $merchant->orders()->where('status', 'menunggu')->count(),
            'latest'  => $merchant->orders()->max('id'),
        ]);
    }

    public function show(Request $request, Order $order)
    {
        $this->own($request, $order);
        $order->load(['items', 'handler']);
        return view('merchant.orders.show', compact('order'));
    }

    // Terima pesanan: status menjadi diproses, stok berkurang, komisi tercatat
    private function acceptOrder(Order $order, int $userId): void
    {
        DB::transaction(function () use ($order, $userId) {
            $order->update(['status' => 'diproses', 'handled_by' => $userId]);

            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)
                        ->update(['stock' => DB::raw('GREATEST(stock - ' . (int) $item->qty . ', 0)')]);
                }
            }

            Commission::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'merchant_id' => $order->merchant_id,
                    'order_total' => $order->total,
                    'rate'        => self::COMMISSION_RATE,
                    'amount'      => (int) round($order->total * self::COMMISSION_RATE / 100),
                ]
            );
        });
    }

    public function accept(Request $request, Order $order)
    {
        $this->own($request, $order);
        abort_unless($order->status === 'menunggu', 422);

        $this->acceptOrder($order, $request->user()->id);

        return back()->with('success', "Pesanan {$order->code} diterima.");
    }

    public function reject(Request $request, Order $order)
    {
        $this->own($request, $order);
        abort_unless($order->status === 'menunggu', 422);

        $data = $request->validate(['reject_reason' => ['required', 'string', 'max:255']]);
        $order->update([
            'status' => 'ditolak',
            'reject_reason' => $data['reject_reason'],
            'handled_by' => $request->user()->id,
        ]);

        return back()->with('success', "Pesanan {$order->code} ditolak.");
    }

    public function complete(Request $request, Order $order)
    {
        $this->own($request, $order);
        abort_unless($order->status === 'diproses', 422);

        $order->update(['status' => 'selesai']);

        return back()->with('success', "Pesanan {$order->code} selesai.");
    }

    // Aksi massal pada pesanan yang dicentang: diproses | selesai | hapus
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'action' => ['required', 'in:diproses,selesai,hapus'],
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer'],
        ], ['ids.required' => 'Pilih minimal satu pesanan.']);

        $merchant = $request->user()->merchant;
        $orders   = $merchant->orders()->with('items')->whereIn('id', $data['ids'])->get();

        if ($data['action'] === 'diproses') {
            $todo = $orders->where('status', 'menunggu');
            $todo->each(fn ($o) => $this->acceptOrder($o, $request->user()->id));
            $message = "{$todo->count()} pesanan diterima dan diproses.";
            $skipped = $orders->count() - $todo->count();
            if ($skipped) $message .= " {$skipped} dilewati karena statusnya bukan Menunggu.";
        } elseif ($data['action'] === 'selesai') {
            $todo = $orders->where('status', 'diproses');
            $todo->each(fn ($o) => $o->update(['status' => 'selesai']));
            $message = "{$todo->count()} pesanan ditandai selesai.";
            $skipped = $orders->count() - $todo->count();
            if ($skipped) $message .= " {$skipped} dilewati karena statusnya bukan Diproses.";
        } else {
            $merchant->orders()->whereIn('id', $orders->pluck('id'))->delete();
            $message = "{$orders->count()} pesanan dihapus.";
        }

        return back()->with('success', $message);
    }
}