<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected $transactionRepo;

    public function __construct(TransactionRepository $transactionRepo)
    {
        $this->transactionRepo = $transactionRepo;
    }

    public function processTransaction($request)
    {
        DB::beginTransaction();
        try {
            $transaction = $this->transactionRepo->createTransaction($request);
            DB::commit();
            return [
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'data' => $transaction
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Transaksi gagal!',
                'error' => $e->getMessage()
            ];
        }
    }
}
