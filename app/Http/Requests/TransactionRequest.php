<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user && ($user->hasRole('superadmin') || $user->hasPermissionTo('create_transaction'));;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'transaction_type_id' => 'required|integer|exists:transaction_types,id',
            'items' => 'required|array|min:1',
            'items.*.barang_kode' => 'required|exists:barangs,barang_kode',
            'items.*.gudang_id' => 'required|exists:gudangs,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
