<?php

namespace App\Services;

use App\Models\Barang;
use App\Repositories\BarangRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BarangService
{
    protected $barangRepository;

    public function __construct(BarangRepository $barangRepository)
    {
        $this->barangRepository = $barangRepository;
    }

    public function getAllBarang()
    {
        return $this->barangRepository->getAll();
    }

    public function getBarangById($id)
    {
        return $this->barangRepository->findById($id);
    }

    public function createBarang(array $data)
    {
        $this->validateCreateData($data);

        $existingBarang = Barang::withTrashed()->where('barang_nama', $data['barang_nama'])->first();

        if ($existingBarang) {
            if ($existingBarang->trashed()) {
                return $this->restoreAndUpdateBarang($existingBarang, $data);
            } else {
                throw ValidationException::withMessages([
                    'barang_nama' => 'Barang dengan nama ini sudah ada.'
                ]);
            }
        }
        $data['barang_slug'] = $this->generateUniqueSlug($data['barang_nama']);
        $data['user_id'] = Auth::id();
        $data['barang_gambar'] = $this->handleImageUpload($data['barang_gambar'] ?? null);

        $barang = $this->barangRepository->create($data);

        if (!empty($data['gudang_id'])) {
            $this->attachGudangStok($barang, $data);
        }

        return $barang;
    }

    public function updateBarang($id, array $data)
    {
        $barang = $this->barangRepository->findById($id);
        if (!$barang) throw new \Exception('Barang tidak ditemukan.');

        $this->validateUpdateData($data, $id);
        $this->ensureUniqueNamaBarang($data['barang_nama'], $id);

        if ($data['barang_nama'] !== $barang->barang_nama) {
            $data['barang_slug'] = $this->generateUniqueSlug($data['barang_nama'], $id);
        }

        if (!empty($data['barang_gambar'])) {
            $data['barang_gambar'] = $this->replaceImage($barang->barang_gambar, $data['barang_gambar']);
        }

        $this->barangRepository->update($barang, $data);

        $this->attachGudangStok($barang, $data);

        return $barang;
    }

    public function deleteBarang($id)
    {
        $barang = $this->barangRepository->findById($id);
        if (!$barang) return null;

        return $this->barangRepository->delete($barang);
    }

    public function restore($id)
    {
        $barang = Barang::onlyTrashed()->find($id);
        if ($barang) $barang->restore();

        return $barang;
    }

    // ================= PRIVATE HELPERS =================

    private function validateCreateData(array $data)
    {
        Validator::make($data, [
            'jenisbarang_id' => 'nullable|exists:jenis_barangs,id',
            'satuan_id' => 'nullable|exists:satuans,id',
            'barangcategory_id' => 'nullable|exists:barang_categories,id',
            'barang_nama' => 'required|string|max:255',
            'barang_harga' => 'required|numeric|min:0',
            'barang_gambar' => 'nullable|string',
            'gudang_id' => 'nullable|exists:gudangs,id',
            'stok_tersedia' => 'required|numeric|min:0',
        ])->validate();
    }

    private function validateUpdateData(array $data, $id)
    {
        Validator::make($data, [
            'barang_nama' => 'required|string|max:255',
            'barang_harga' => 'required|numeric|min:0',
            'gudang_id' => 'required|exists:gudangs,id',
            'stok_tersedia' => 'sometimes|numeric|min:0',
        ])->validate();
    }

    private function findSoftDeletedBarangByName($nama)
    {
        return Barang::onlyTrashed()->where('barang_nama', $nama)->first();
    }

    private function restoreAndUpdateBarang(Barang $barang, array $data)
    {
        $barang->restore();

        $updateData = [
            'barang_harga' => $data['barang_harga'],
            'jenisbarang_id' => $data['jenisbarang_id'] ?? null,
            'satuan_id' => $data['satuan_id'] ?? null,
            'barangcategory_id' => $data['barangcategory_id'] ?? null,
            'user_id' => Auth::id(),
            'barang_slug' => $this->generateUniqueSlug($data['barang_nama']),
        ];

        if (!empty($data['barang_gambar'])) {
            $updateData['barang_gambar'] = $this->handleImageUpload($data['barang_gambar']);
        }

        $this->barangRepository->update($barang, $updateData);

        $this->attachGudangStok($barang, $data);

        return $barang;
    }

    private function generateUniqueSlug($nama, $excludeId = null)
    {
        $baseSlug = Str::slug($nama);
        $slug = $baseSlug;
        $count = Barang::where('barang_slug', $baseSlug)
            ->when($excludeId, fn($query) => $query->where('id', '!=', $excludeId))
            ->count();

        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        return $slug;
    }

    private function ensureUniqueNamaBarang($nama, $excludeId)
    {
        $exists = Barang::where('barang_nama', $nama)
            ->where('id', '!=', $excludeId)
            ->exists();

        if ($exists) {
            throw new \Illuminate\Validation\ValidationException(
                Validator::make([], []),
                response()->json(['errors' => ['barang_nama' => 'Nama barang ini sudah digunakan!']], 422)
            );
        }
    }

    private function handleImageUpload($base64Image)
    {
        return $base64Image ? uploadBase64Image($base64Image) : 'default_image.png';
    }

    private function replaceImage($oldImage, $newBase64)
    {
        if ($oldImage && $oldImage !== 'default_image.png') {
            Storage::disk('public')->delete($oldImage);
        }
        return uploadBase64Image($newBase64);
    }

    private function attachGudangStok(Barang $barang, array $data)
    {
        $barang->gudangs()->syncWithoutDetaching([
            $data['gudang_id'] => [
                'stok_tersedia' => $data['stok_tersedia'] ?? 0,
                'stok_dipinjam' => 0,
                'stok_maintenance' => 0,
            ]
        ]);
    }
}
