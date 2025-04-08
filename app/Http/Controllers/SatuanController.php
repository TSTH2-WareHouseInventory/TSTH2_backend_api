<?php

namespace App\Http\Controllers;

use App\Http\Requests\SatuanRequest;
use App\Http\Resources\SatuanResource;
use App\Models\Satuan;
use App\Services\SatuanService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SatuanController extends Controller
{
    protected $satuanService;

    public function __construct(SatuanService $satuanService)
    {
        $this->satuanService = $satuanService;
    }

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware('permission:view_satuan', only: ['index', 'show']),
            new Middleware('permission:create_satuan', only: ['store']),
            new Middleware('permission:update_satuan', only: ['update']),
            new Middleware('permission:delete_satuan', only: ['destroy']),
        ];
    }

    public function index()
    {
        $satuans = $this->satuanService->getAll();
        return response()->json([
            'success' => true,
            'message' => 'Daftar satuan berhasil diambil',
            'data' => SatuanResource::collection($satuans)
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            # check existing name dalam deleted_at
            $existingSatuan = $this->satuanService->findTrashedByName($data['name']);
            if ($existingSatuan) {
                // Restore satuan jika ada di trash
                $satuan = $this->satuanService->restore($existingSatuan->id);
            } else {
                $satuan = $this->satuanService->create($data);
            }

            return response()->json([
                'success' => true,
                'message' => 'Satuan barang berhasil ditambahkan!',
                'data' => new SatuanResource($satuan)
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal!', 'errors' => $e->errors()], 422);
        }
    }

    public function show($id)
    {
        $satuan = $this->satuanService->getById($id);
        if (!$satuan) {
            return response()->json(['message' => 'Satuan barang tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => new SatuanResource($satuan)], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $satuan = $this->satuanService->update($id, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Satuan barang berhasil diubah!',
                'data' => new SatuanResource($satuan)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $this->satuanService->delete($id);
        return response()->json(['success' => true, 'message' => 'Satuan barang berhasil dihapus'], 200);
    }
}
