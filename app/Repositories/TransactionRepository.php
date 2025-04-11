<?php

namespace App\Repositories;

use App\Models\{Transaction, TransactionDetail, Barang, BarangGudang};
use Exception;

class TransactionRepository
{
    public function createTransaction($request)
    {
        $gudangId = null;

        $transaction = Transaction::create([
            'user_id' => $request->user_id,
            'transaction_type_id' => $request->transaction_type_id,
            'transaction_code' => $this->generateTransactionCode($request->transaction_type_id),
            'transaction_date' => now(),
        ]);

        foreach ($request->items as $item) {
            // **Validasi barang harus berasal dari gudang yang sama**
            if ($gudangId === null) {
                $gudangId = $item['gudang_id'];
            } elseif ($gudangId !== $item['gudang_id']) {
                throw new Exception('Barang harus berasal dari gudang yang sama.');
            }

            $this->processTransactionItem($transaction->id, $item, $request->transaction_type_id);
        }

        return $transaction;
    }


    private function generateTransactionCode($typeId)
    {
        $prefixes = [1 => 'MSK', 2 => 'KLR', 3 => 'PJM', 4 => 'KMB'];
        $prefix = $prefixes[$typeId] ?? 'UNK';
        $lastTransaction = Transaction::where('transaction_type_id', $typeId)->latest('id')->first();
        $number = $lastTransaction ? str_pad($lastTransaction->id + 1, 3, '0', STR_PAD_LEFT) : '001';
        return "TRX-{$prefix}-{$number}";
    }

    private function processTransactionItem($transactionId, $item, $transactionType)
    {
        $barang = Barang::where('barang_kode', $item['barang_kode'])->first();
        $barangGudang = BarangGudang::where('barang_id', $barang->id)->where('gudang_id', $item['gudang_id'])->first();

        if (!$barangGudang) {
            throw new Exception("Barang {$barang->barang_nama} tidak tersedia di gudang!");
        }

        // **Validasi tipe transaksi berdasarkan kategori barang**
        $validTransactionType = true;
        if ($barang->barangcategory_id == 1 && !in_array($transactionType, [1, 2])) {
            $validTransactionType = false;
        } elseif ($barang->barangcategory_id == 2 && !in_array($transactionType, [1, 3, 4])) {
            $validTransactionType = false;
        }

        if (!$validTransactionType) {
            throw new Exception("Jenis transaksi tidak valid untuk barang {$barang->barang_nama}!");
        }

        // **Lanjutkan transaksi setelah validasi**
        if ($transactionType == 2 && $barangGudang->stok_tersedia < $item['quantity']) {
            throw new Exception("Stok tidak mencukupi untuk barang {$barang->barang_nama}!");
        }

        if ($transactionType == 3 && $barangGudang->stok_tersedia < $item['quantity']) {
            throw new Exception("Barang {$barang->barang_nama} tidak cukup untuk dipinjam!");
        }

        switch ($transactionType) {
            case 1: // Barang Masuk
                BarangGudang::where('barang_id', $barang->id)
                    ->where('gudang_id', $item['gudang_id'])
                    ->increment('stok_tersedia', $item['quantity']);
                break;
            case 2: // Barang Keluar
                BarangGudang::where('barang_id', $barang->id)
                    ->where('gudang_id', $item['gudang_id'])
                    ->decrement('stok_tersedia', $item['quantity']);
                break;
            case 3: // Peminjaman
                BarangGudang::where('barang_id', $barang->id)
                    ->where('gudang_id', $item['gudang_id'])
                    ->decrement('stok_tersedia', $item['quantity']);
                BarangGudang::where('barang_id', $barang->id)
                    ->where('gudang_id', $item['gudang_id'])
                    ->increment('stok_dipinjam', $item['quantity']);
                break;
            case 4: // Pengembalian
                if ($barangGudang->stok_dipinjam < $item['quantity']) {
                    throw new Exception("Barang dikembalikan lebih banyak dari yang dipinjam! ({$barang->barang_nama})");
                }
                BarangGudang::where('barang_id', $barang->id)
                    ->where('gudang_id', $item['gudang_id'])
                    ->increment('stok_tersedia', $item['quantity']);
                BarangGudang::where('barang_id', $barang->id)
                    ->where('gudang_id', $item['gudang_id'])->decrement('stok_dipinjam', $item['quantity']);
                break;
        }

        TransactionDetail::create([
            'transaction_id' => $transactionId,
            'barang_id' => $barang->id,
            'gudang_id' => $item['gudang_id'],
            'quantity' => $item['quantity'],
            'tanggal_kembali' => ($transactionType == 4) ? now() : null
        ]);
    }
}
