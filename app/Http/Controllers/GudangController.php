<?php

namespace App\Http\Controllers;

use App\Http\Resources\GudangResource;
use App\Services\GudangService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class GudangController extends Controller
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
            $gudang = $this->gudangService->create($request->all());
            return response()->json([
                'message' => 'Data gudang berhasil dibuat',
                'data' => new GudangResource($gudang)
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 400);
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
            return $updated ? response()->json(['message' => 'Gudang berhasil diperbarui']) : response()->json([
                'message' => 'Gudang tidak ditemukan'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 400);
        }
    }
    public function destroy($id)
    {
        $deleted = $this->gudangService->delete($id);
        return $deleted ? response()->json(['message' => 'Gudang berhasil dihapus']) : response()->json(['message' => 'Gudang tidak ditemukan'], 404);
    }

    // Mengembalikan gudang yang telah dihapus
    public function restore($id)
    {
        $gudang = $this->gudangService->restore($id);
        return $gudang ? new GudangResource($gudang) : response()->json(['message' => 'Gudang tidak ditemukan'], 404);
    }

    // Menghapus gudang secara permanen
    public function forceDelete($id)
    {
        return $this->gudangService->forceDelete($id)
            ? response()->json(['message' => 'Gudang berhasil dihapus permanen'])
            : response()->json(['message' => 'Gudang tidak ditemukan'], 404);
    }
}
