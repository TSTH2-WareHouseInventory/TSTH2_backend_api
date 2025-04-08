<?php

namespace App\Http\Controllers;

use App\Http\Resources\BarangResource;
use App\Models\Barang;
use App\Services\BarangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarangController extends Controller implements HasMiddleware
{
    protected $barangService;
    public static function middleware(): array
    {
        return [
            'auth:api',
            // new Middleware('permission:view_barang', only: ['index']),
            new Middleware('permission:create_barang', only: ['store']),
            new Middleware('permission:update_barang', only: ['update']),
            new Middleware('permission:delete_barang', only: ['destroy']),
        ];
    }

    public function __construct(BarangService $barangService)
    {
        $this->barangService = $barangService;
    }

    public function index()
    {
        $barangs = $this->barangService->getAllBarang();
        return BarangResource::collection($barangs);
    }
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $barang = $this->barangService->createBarang($data);
            return response()->json([
                'message' => 'Barang berhasil dibuat!',
                'data' => new BarangResource($barang)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        $barang = $this->barangService->getBarangById($id);
        if (!$barang) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }
        return new BarangResource($barang);
    }

    public function update(Request $request, $id)
    {
        $barang = $this->barangService->updateBarang($id, $request->all());
        if (!$barang) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }
        return response()->json([
            'message' => 'Barang berhasil diperbarui!',
            'data' =>  new BarangResource($barang)
        ]);
    }

    public function destroy($id)
    {
        $deleted = $this->barangService->deleteBarang($id);
        if (!$deleted) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }
        return response()->json(['message' => 'Barang berhasil dihapus'], 200);
    }
}
