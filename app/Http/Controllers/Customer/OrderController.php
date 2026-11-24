<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\OrderHistory;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Riwayat pesanan di browser ini
    public function history(Request $request)
    {
        $tokens = OrderHistory::tokens($request);

        $orders = $tokens
            ? Order::with(['items', 'reviews'])->whereIn('token', $tokens)->latest()->get()
            : collect();

        return view('customer.history', compact('orders'));
    }

    // Status terbaru semua pesanan di riwayat (untuk pembaruan otomatis)
    public function historyStatus(Request $request)
    {
        $tokens = OrderHistory::tokens($request);

        return response()->json($tokens ? Order::whereIn('token', $tokens)->pluck('status', 'token') : (object) []);
    }

    // Hapus satu pesanan dari riwayat (pesanan di sisi penjual tidak terhapus)
    public function forgetHistory(Request $request, string $token)
    {
        OrderHistory::forget($request, $token);

        return redirect()->route('customer.history')->with('success', 'Pesanan dihapus dari riwayat.');
    }

    // Batalkan pesanan: hanya jika penjual belum menerimanya
    public function cancel(Request $request, string $token)
    {
        $order = Order::where('token', $token)->firstOrFail();

        // Update bersyarat: jika admin menerima pesanan di detik yang sama, pembatalan gagal dan tidak menimpa status
        $affected = Order::where('id', $order->id)
            ->where('status', 'menunggu')
            ->update(['status' => 'dibatalkan']);

        if ($affected === 0) {
            return redirect()->route('customer.history')
                ->with('error', "Pesanan #{$order->code} sudah diproses penjual, jadi tidak bisa dibatalkan lagi.");
        }

        OrderHistory::remember($request, $token);

        return redirect()->route('customer.history')
            ->with('success', "Pesanan #{$order->code} dibatalkan.");
    }

    // Hapus seluruh riwayat di browser ini
    public function clearHistory()
    {
        OrderHistory::clear();

        return redirect()->route('customer.history')->with('success', 'Seluruh riwayat dihapus dari browser ini.');
    }

    // Halaman satu pesanan: status, pembayaran, rating dan ulasan
    public function show(Request $request, string $token)
    {
        $order = Order::with(['items', 'reviews', 'merchant'])->where('token', $token)->firstOrFail();

        OrderHistory::remember($request, $token);   // pesanan dari tautan ikut masuk riwayat browser ini

        return view('customer.order', compact('order'));
    }

    public function status(string $token)
    {
        return response()->json(['status' => Order::where('token', $token)->firstOrFail()->status]);
    }
}