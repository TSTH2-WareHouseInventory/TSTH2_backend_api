<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SatuanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;  // Atur sesuai dengan aturan otorisasi jika diperlukan
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:satuans,name'],
            'description' => ['nullable', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' w=> 'Nama satuan barang wajib diisi.',
            'name.unique' => 'Nama satuan barang sudah digunakan.',
        ];
    }
}
