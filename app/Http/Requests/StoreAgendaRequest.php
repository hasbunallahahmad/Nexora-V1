<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgendaRequest extends FormRequest
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
            'judul_agenda' => [
                'required',
                'string',
                'min:5',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\&\-\.\,\(\)]+$/u',
            ],
            'deskripsi' => [
                'nullable',
                'string',
                'max:150',
            ],
            'location' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\&\-\.\,\(\)]+$/u',
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'is_published' => [
                'boolean',
            ],
            'bidang_id' => [
                'nullable',
                'array',
            ],
            'bidang_id.*' => [
                'integer',
                'exists:bidang,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'judul_agenda.required' => 'Judul agenda wajib diisi.',
            'judul_agenda.min' => 'Judul agenda minimal 5 karakter.',
            'judul_agenda.max' => 'Judul agenda maksimal 100 karakter.',
            'judul_agenda.regex' => 'Judul agenda hanya boleh berisi huruf, angka, dan simbol (&-.,()).',
            'deskripsi.max' => 'Deskripsi maksimal 150 karakter.',
            'location.required' => 'Lokasi wajib diisi.',
            'location.min' => 'Lokasi minimal 3 karakter.',
            'location.max' => 'Lokasi maksimal 100 karakter.',
            'location.regex' => 'Lokasi hanya boleh berisi huruf, angka, dan simbol (&-.,()).',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh lebih awal dari hari ini.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'is_published.boolean' => 'Status publikasi harus berupa nilai boolean.',
            'bidang_id.array' => 'Bidang harus berupa array.',
            'bidang_id.*.integer' => 'Setiap ID bidang harus berupa angka.',
            'bidang_id.*.exists' => 'Salah satu bidang tidak ditemukan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'judul_agenda' => 'Judul Agenda',
            'deskripsi' => 'Deskripsi',
            'location' => 'Lokasi',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'is_published' => 'Status Publikasi',
            'bidang_id' => 'Bidang',
        ];
    }
}
