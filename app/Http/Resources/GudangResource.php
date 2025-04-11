<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GudangResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'stok_tersedia' => $this->stok_tersedia ?? $this->pivot->stok_tersedia ?? 0,
            'stok_dipinjam' => $this->stok_dipinjam ?? 0,
            'stok_maintenance' => $this->stok_maintenance ?? 0,
            'created_at' => $this->created_at ? date('Y-m-d H:i:s', strtotime($this->created_at)) : null,
            'updated_at' => $this->updated_at ? date('Y-m-d H:i:s', strtotime($this->updated_at)) : null,
            'deleted_at' => $this->deleted_at ? date('Y-m-d H:i:s', strtotime($this->deleted_at)) : null,
        ];
    }
}
