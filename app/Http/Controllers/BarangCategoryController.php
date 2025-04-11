<?php

namespace App\Http\Controllers;

use App\Http\Resources\BarangCategoryResource;
use App\Services\BarangCategoryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class BarangCategoryController extends Controller
{
    protected $barangCategoryService;

    public function __construct(BarangCategoryService $barangCategoryService)
    {
        $this->barangCategoryService = $barangCategoryService;
    }

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware('permission:view_category_barang', only: ['index', 'show']),
            new Middleware('permission:create_category_barang', only: ['store']),
            new Middleware('permission:update_category_barang', only: ['update']),
            new Middleware('permission:delete_category_barang', only: ['destroy']),
        ];
    }

    public function index()
    {
        $barangCategories = $this->barangCategoryService->getAll();
        return response()->json([
            'message' => 'Data kategori barang berhasil diambil',
            'data' => BarangCategoryResource::collection($barangCategories),
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $barangCategory = $this->barangCategoryService->create($request->all());
            return response()->json([
                'message' => 'Kategori barang berhasil ditambahkan!',
                'data' => new BarangCategoryResource($barangCategory)
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambah kategori barang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $barangCategory = $this->barangCategoryService->getById($id);

        if (!$barangCategory) {
            return response()->json(['message' => 'Kategori barang tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Kategori barang berhasil diambil',
            'data' => new BarangCategoryResource($barangCategory),
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $barangCategory = $this->barangCategoryService->update($id, $request->all());

            return response()->json([
                'message' => 'Kategori barang berhasil diubah!',
                'data' => new BarangCategoryResource($barangCategory)
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengubah kategori barang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->barangCategoryService->delete($id);
            return response()->json([
                'message' => 'Kategori barang berhasil dihapus!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus kategori barang',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
