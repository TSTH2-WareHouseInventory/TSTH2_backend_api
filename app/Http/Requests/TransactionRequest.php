<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->hasRole('superadmin') || $user->hasPermissionTo('create_transaction'));
    }

    public function rules(): array
    {
        return [
            'transaction_type_id' => 'required|integer|exists:transaction_types,id',
            'items' => 'required|array|min:1',
            'items.*.barang_kode' => 'required|exists:barangs,barang_kode',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
