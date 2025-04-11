<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionTypeResource;
use App\Services\TransactionTypeService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

use Illuminate\Validation\ValidationException;

class TransactionTypeController extends Controller
{
    protected $transactionTypeService;

    public function __construct(TransactionTypeService $transactionTypeService)
    {
        $this->transactionTypeService = $transactionTypeService;
    }

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware('permission:view_transaction_type', only: ['index', 'show']),
            new Middleware('permission:create_transaction_type', only: ['store']),
            new Middleware('permission:update_transaction_type', only: ['update']),
            new Middleware('permission:delete_transaction_type', only: ['destroy']),
        ];
    }
    public function index()
    {
        $transactionTypes = $this->transactionTypeService->getAll();
        return response()->json([
            'message' => 'Tipe transaksi berhasil diambil',
            'data' => TransactionTypeResource::collection($transactionTypes),
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $transactionType = $this->transactionTypeService->create($request->all());
            return response()->json([
                'message' => 'Tipe transaksi berhasil ditambahkan!',
                'data' => new TransactionTypeResource($transactionType)
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambah tipe transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $transactionType = $this->transactionTypeService->getById($id);

        if (!$transactionType) {
            return response()->json(['message' => 'Tipe transaksi tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Tipe transaksi berhasil diambil',
            'data' => new TransactionTypeResource($transactionType),
        ], 200);
    }

    public function update(Request $request, $id)
    {
        // try {
        $transactionType = $this->transactionTypeService->update($id, $request->all());

        return response()->json([
            'message' => 'Tipe transaksi berhasil diubah!',
            'data' => new TransactionTypeResource($transactionType)
        ], 200);
        // } catch (ValidationException $e) {
        //     return response()->json([
        //         'message' => 'Validasi gagal!',
        //         'errors' => $e->errors()
        //     ], 422);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'message' => 'Gagal mengubah tipe transaksi',
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
    }

    public function destroy($id)
    {
        try {
            $this->transactionTypeService->delete($id);
            return response()->json([
                'message' => 'Tipe transaksi berhasil dihapus!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus tipe transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
