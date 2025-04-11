<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetailResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'barang' => [
                'id' => $this->barang->id,
                'kode' => $this->barang->barang_kode,
                'nama' => $this->barang->barang_nama,
            ],
            'gudang' => [
                'id' => $this->gudang->id,
                'nama' => $this->gudang->name,
            ],
            'quantity' => $this->quantity,
            'tanggal_kembali' => $this->tanggal_kembali
                ? date('Y-m-d H:i:s', strtotime($this->tanggal_kembali))
                : null,

        ];
    }
}
