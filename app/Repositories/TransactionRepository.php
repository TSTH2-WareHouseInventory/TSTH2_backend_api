<?php

namespace App\Repositories;

use App\Models\{Transaction, TransactionDetail, Barang, BarangGudang, Gudang};
use Illuminate\Support\Facades\Auth;
use Exception;

class TransactionRepository
{
    public function createTransaction($request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $gudangId = $this->getGudangIdByUserId($userId);

        $transaction = Transaction::create([
            'user_id' => $userId,
            'transaction_type_id' => $request->transaction_type_id,
            'transaction_code' => $this->generateTransactionCode($request->transaction_type_id),
            'transaction_date' => now(),
        ]);

        foreach ($request->items as $item) {
            $item['gudang_id'] = $gudangId;
            $this->processTransactionItem($transaction->id, $item, $request->transaction_type_id);
        }

        return $transaction->load([
            'user:id,name',
            'transactionType:id,name',
            'transactionDetails.barang:id,barang_kode,barang_nama',
            'transactionDetails.gudang:id,name'
        ]);
    }

    private function getGudangIdByUserId($userId)
    {
        $gudang = Gudang::where('user_id', $userId)->first();
        if (!$gudang) {
            throw new Exception("Tidak ditemukan gudang yang terdaftar untuk user login.");
        }
        return $gudang->id;
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
        $barang = Barang::where('barang_kode', $item['barang_kode'])->firstOrFail();

        $barangGudang = BarangGudang::where('barang_id', $barang->id)
            ->where('gudang_id', $item['gudang_id'])
            ->first();

        // Validasi berdasarkan tipe transaksi
        if (in_array($transactionType, [2, 3, 4]) && !$barangGudang) {
            throw new Exception("Barang {$barang->barang_nama} belum tersedia di gudang. Masukkan terlebih dahulu.");
        }



        // Validasi kategori barang vs transaksi
        $this->validateItemTransaction($barang, $barangGudang, $item, $transactionType);

        // Proses transaksi
        match ($transactionType) {
            1 => $this->handleBarangMasuk($barang, $item),
            2 => $this->handleBarangKeluar($barang->id, $item),
            3 => $this->handlePeminjaman($barang->id, $item),
            4 => $this->handlePengembalian($barang->id, $item),
        };

        // Catat ke transaction_detail
        TransactionDetail::create([
            'transaction_id' => $transactionId,
            'barang_id' => $barang->id,
            'gudang_id' => $item['gudang_id'],
            'quantity' => $item['quantity'],
            'tanggal_kembali' => $transactionType == 4 ? now() : null,
        ]);
    }

    public function validateItemTransaction($barang, $barangGudang, $item, $transactionType)
    {
        // Validasi kategori barang vs transaksi
        $validTransactionType = match (true) {
            $barang->barangcategory_id == 1 => in_array($transactionType, [1, 2]),
            $barang->barangcategory_id == 2 => in_array($transactionType, [1, 3, 4]),
            default => false,
        };

        if (!$validTransactionType) {
            throw new Exception("Jenis transaksi tidak valid untuk barang {$barang->barang_nama}.");
        }

        // Validasi stok
        if (in_array($transactionType, [2, 3]) && (!$barangGudang || $barangGudang->stok_tersedia < $item['quantity'])) {
            throw new Exception("Stok tidak mencukupi untuk barang {$barang->barang_nama}.");
        }

        if ($transactionType == 4 && (!$barangGudang || $barangGudang->stok_dipinjam < $item['quantity'])) {
            throw new Exception("Barang {$barang->barang_nama} dikembalikan lebih banyak dari yang dipinjam.");
        }
    }

    private function getOrCreateBarangGudang($barangId, $gudangId)
    {
        $barangGudang = BarangGudang::firstOrCreate(
            [
                'barang_id' => $barangId,
                'gudang_id' => $gudangId
            ],
            ['stok_tersedia' => 0, 'stok_dipinjam' => 0, 'stok_maintenance' => 0]
        );
        return $barangGudang;
    }


    private function handleBarangMasuk($barang, $item)
    {
        // if (!$barangGudang) {
        //     // Barang belum terdaftar di gudang, buat relasi baru
        //     $barang->gudangs()->attach($item['gudang_id'], [
        //         'stok_tersedia' => $item['quantity'],
        //         'stok_dipinjam' => 0,
        //         'stok_maintenance' => 0,
        //     ]);
        // } else {
        $this->getOrCreateBarangGudang($barang->id, $item['gudang_id']);
        BarangGudang::where('barang_id', $barang->id)
            ->where('gudang_id', $item['gudang_id'])
            ->increment('stok_tersedia', $item['quantity']);
        // }
    }

    private function handleBarangKeluar($barangId, $item)
    {
        BarangGudang::where('barang_id', $barangId)
            ->where('gudang_id', $item['gudang_id'])
            ->decrement('stok_tersedia', $item['quantity']);
    }

    private function handlePeminjaman($barangId, $item)
    {
        BarangGudang::where('barang_id', $barangId)
            ->where('gudang_id', $item['gudang_id'])
            ->decrement('stok_tersedia', $item['quantity']);

        BarangGudang::where('barang_id', $barangId)
            ->where('gudang_id', $item['gudang_id'])
            ->increment('stok_dipinjam', $item['quantity']);
    }

    private function handlePengembalian($barangId, $item)
    {
        BarangGudang::where('barang_id', $barangId)
            ->where('gudang_id', $item['gudang_id'])
            ->increment('stok_tersedia', $item['quantity']);

        BarangGudang::where('barang_id', $barangId)
            ->where('gudang_id', $item['gudang_id'])
            ->decrement('stok_dipinjam', $item['quantity']);
    }
}
