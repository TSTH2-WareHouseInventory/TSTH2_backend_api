<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarangResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barang_kode' => $this->barang_kode,
            'barang_nama' => $this->barang_nama,
            'barang_slug' => $this->barang_slug,
            'barang_harga' => $this->barang_harga,
            'barangcategory_id' => $this->barangcategory_id,
            'category' => $this->category ? $this->category->name : null,
            'satuan' => $this->satuan ? $this->satuan->name : null,
            'jenisBarang' => $this->jenisBarang ? $this->jenisBarang->name : null,
            'barang_gambar' => asset('storage/' . $this->barang_gambar),
            'satuan_id' => $this->satuan_id,
            'jenisbarang_id' => $this->jenisbarang_id,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'gudangs' => GudangResource::collection($this->gudangs),
        ];
    }
}
