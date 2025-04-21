<?php

namespace App\Http\Controllers;

use App\Http\Resources\GudangResource;
use App\Services\GudangService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class GudangController extends Controller implements HasMiddleware
{
    protected $gudangService;

    public function __construct(GudangService $gudangService)
    {
        $this->gudangService = $gudangService;
    }

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware('permission:view_gudang', only: ['index', 'show']),
            new Middleware('permission:create_gudang', only: ['store']),
            new Middleware('permission:update_gudang', only: ['update']),
            new Middleware('permission:delete_gudang', only: ['destroy']),
        ];
    }

    public function index()
    {
        $gudang = $this->gudangService->getAll();
        return GudangResource::collection($gudang);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->only(['name', 'description', 'user_id']);

            $existingGudang = $this->gudangService->findTrashedByName($data['name']);
            if ($existingGudang) {
                $gudang = $this->gudangService->restore($existingGudang->id);
                return response()->json([
                    'success' => true,
                    'message' => 'Data gudang yang sebelumnya dihapus telah berhasil direstore.',
                    'restored' => true,
                    'data' => new GudangResource($gudang)
                ], 200); // Set status code eksplisit OK
            }

            $gudang = $this->gudangService->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Data gudang berhasil dibuat.',
                'restored' => false,
                'data'    => new GudangResource($gudang),
            ], 201); // Created
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data gudang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        $gudang = $this->gudangService->getById($id);

        return $gudang ? new GudangResource($gudang) : response()->json(['message' => 'Gudang tidak ditemukan'], 404);
    }

    public function update(Request $request, $id)
    {
        try {
            $updated = $this->gudangService->update($id, $request->all());
            $gudang = $this->gudangService->getById($id);
            return $updated ? response()->json([
                'success' => true,
                'message' => 'Gudang berhasil diperbarui',
                'data' => new GudangResource($gudang),
            ]) : response()->json([
                'success' => false,
                'message' => 'Gudang tidak ditemukan',
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 400);
        }
    }
    public function destroy($id)
    {
        $deleted = $this->gudangService->delete($id);
        return $deleted ? response()->json(['success' => true, 'message' => 'Gudang berhasil dihapus']) : response()->json(['success' => false, 'message' => 'Gudang tidak ditemukan'], 404);
    }

    // Mengembalikan gudang yang telah dihapus
    public function restore($id)
    {
        $gudang = $this->gudangService->restore($id);
        return $gudang ? new GudangResource($gudang) : response()->json(['success' => false, 'message' => 'Gudang tidak ditemukan'], 404);
    }

    // Menghapus gudang secara permanen
    public function forceDelete($id)
    {
        return $this->gudangService->forceDelete($id)
            ? response()->json(['message' => 'Gudang berhasil dihapus permanen'])
            : response()->json(['message' => 'Gudang tidak ditemukan'], 404);
    }
}
