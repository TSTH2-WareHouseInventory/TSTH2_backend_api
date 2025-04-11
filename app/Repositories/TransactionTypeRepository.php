<?php

namespace App\Repositories;

use App\Models\TransactionType;

class TransactionTypeRepository
{
    public function getAll()
    {
        return TransactionType::all();
    }

    public function findById($id)
    {
        return TransactionType::find($id);
    }

    public function create(array $data)
    {
        return TransactionType::create($data);
    }

    public function update(TransactionType $transactionType, array $data)
    {
        return $transactionType->update($data);
    }

    public function delete(TransactionType $transactionType)
    {
        return $transactionType->delete();
    }
}
