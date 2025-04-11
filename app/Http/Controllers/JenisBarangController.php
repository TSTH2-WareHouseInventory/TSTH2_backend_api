<?php

namespace App\Http\Controllers;

use App\Services\JenisBarangService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Exception;

class JenisBarangController extends Controller
{
    protected $jenisBarangService;

    public function __construct(JenisBarangService $jenisBarangService)
    {
        $this->jenisBarangService = $jenisBarangService;
    }

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware('permission:view_jenis_barang', only: ['index', 'show']),
            new Middleware('permission:create_jenis_barang', only: ['store']),
            new Middleware('permission:update_jenis_barang', only: ['update']),
            new Middleware('permission:delete_jenis_barang', only: ['destroy']),
        ];
    }

    public function index()
    {
        $jenisBarang = $this->jenisBarangService->getAll();
        return response()->json(['success' => true, 'message' => 'Daftar jenis barang berhasil diambil', 'data' => $jenisBarang]);
    }

    public function store(Request $request)
    {
        try {
            $jenisBarang = $this->jenisBarangService->create($request->all());
            return response()->json(['success' => true, 'message' => 'Jenis barang berhasil ditambahkan', 'data' => $jenisBarang], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors()], 422);
        }
    }

    public function show($id)
    {
        $jenisBarang = $this->jenisBarangService->getById($id);
        if (!$jenisBarang) {
            return response()->json(['message' => 'Jenis barang tidak ditemukan'], 404);
        }
        return response()->json(['success' => true, 'data' => $jenisBarang]);
    }

    public function update(Request $request, $id)
    {
        try {
            $jenisBarang = $this->jenisBarangService->update($id, $request->all());
            return response()->json(['success' => true, 'message' => 'Jenis barang berhasil diubah', 'data' => $jenisBarang]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $deleted = $this->jenisBarangService->delete($id);
        if (!$deleted) {
            return response()->json(['message' => 'Jenis Barang tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'message' => 'Jenis barang berhasil dihapus!']);
    }

    public function restore($id)
    {
        $jenisBarang = $this->jenisBarangService->restore($id);
        return response()->json(['success' => true, 'message' => 'Jenis barang berhasil dikembalikan', 'data' => $jenisBarang]);
    }

    public function forceDelete($id)
    {
        $this->jenisBarangService->forceDelete($id);
        return response()->json(['success' => true, 'message' => 'Jenis barang berhasil dihapus permanen']);
    }
}
