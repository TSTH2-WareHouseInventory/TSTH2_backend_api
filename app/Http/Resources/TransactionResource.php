<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'transaction_code' => $this->transaction_code,
            'transaction_date' => $this->transaction_date ? date('Y-m-d H:i:s', strtotime($this->transaction_date)) : null,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'transaction_type' => [
                'id' => $this->transactionType->id,
                'name' => $this->transactionType->name,
            ],
            'items' => TransactionDetailResource::collection($this->transactionDetails),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
