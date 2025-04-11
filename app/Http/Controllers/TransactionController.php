<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Routing\Controllers\Middleware;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware('permission:view_transaction', only: ['index']),
            new Middleware('permission:create_transaction', only: ['store']),
        ];
    }


    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'transactionType', 'transactionDetails.barang', 'transactionDetails.gudang']);

        if (!$request->user()->hasRole('superadmin')) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('transaction_type_id')) {
            $query->where('transaction_type_id', $request->transaction_type_id);
        }

        if ($request->filled('transaction_code')) {
            $query->where('transaction_code', 'LIKE', "%{$request->transaction_code}%");
        }

        if ($request->filled(['transaction_date_start', 'transaction_date_end'])) {
            $query->whereBetween('transaction_date', [$request->transaction_date_start, $request->transaction_date_end]);
        }

        return TransactionResource::collection($query->paginate(10));
    }

    public function store(TransactionRequest $request)
    {
        $result = $this->transactionService->processTransaction($request);

        if (!$result['success']) {
            return response()->json(['message' => $result['message'], 'error' => $result['error']], 422);
        }

        return response()->json([
            'message' => 'Transaction berhasil dibuat!',
            'data' => new TransactionResource($result['data'])
        ]);
    }
}
