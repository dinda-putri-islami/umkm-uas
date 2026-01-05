<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function __construct()
    {
        // Pastikan semua method butuh autentikasi
        $this->middleware('auth:sanctum');
    }

    // List transaksi user
    public function index()
    {
        $transactions = Transaction::with('items.product')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'message' => 'List transaksi',
            'data' => $transactions
        ]);
    }

    // Simpan transaksi baru
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $total = 0;

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'total' => 0,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $price = $product->harga;
                $subtotal = $price * $item['qty'];

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $transaction->update(['total' => $total]);

            DB::commit();

            return response()->json([
                'message' => 'Transaksi berhasil',
                'data' => $transaction->load('items.product')
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Detail transaksi
    public function show($id)
    {
        $transaction = Transaction::with('items.product')
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail(); // aman dari transaksi milik orang lain

        return response()->json([
            'message' => 'Detail transaksi',
            'data' => $transaction
        ]);
    }

    // Update transaksi tidak diizinkan
    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Update transaksi tidak diizinkan'
        ], 403);
    }

    // Hapus transaksi
    public function destroy($id)
    {
        $transaction = Transaction::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $transaction->delete();

        return response()->json([
            'message' => 'Transaksi berhasil dihapus'
        ]);
    }
}
