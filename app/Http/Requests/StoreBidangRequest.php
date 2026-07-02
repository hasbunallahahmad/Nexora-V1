<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBidangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_bidang' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z\s\&\-\.\,]+$/',
                'unique:bidang,nama_bidang',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:25',
                'alpha_dash',
                'unique:bidang,slug',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_bidang.required' => 'Nama bidang wajib diisi.',
            'nama_bidang.min' => 'Nama bidang minimal 3 karakter.',
            'nama_bidang.max' => 'Nama bidang maksimal 30 karakter.',
            'nama_bidang.regex' => 'Nama bidang hanya boleh berisi huruf, spasi, dan simbol (&-.)).',
            'nama_bidang.unique' => 'Nama bidang ini sudah ada dalam sistem.',
            'slug.alpha_dash' => 'Slug hanya boleh berisi huruf, angka, dash (-) dan underscore (_).',
            'slug.unique' => 'Slug ini sudah digunakan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_bidang' => 'Nama Bidang',
            'slug' => 'Slug',
        ];
    }
}
