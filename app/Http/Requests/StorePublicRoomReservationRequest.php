<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Facility\Rules\HoneypotEmpty;
use App\Facility\Rules\ValidTurnstile;
use Illuminate\Foundation\Http\FormRequest;

class StorePublicRoomReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'               => ['required', 'integer', 'exists:rooms,id'],
            'title'                 => ['required', 'string', 'min:5', 'max:150'],
            'purpose'               => ['nullable', 'string', 'max:255'],
            'start_datetime'        => ['required', 'date', 'after:now'],
            'end_datetime'          => ['required', 'date', 'after:start_datetime'],
            'guest_name'            => ['required', 'string', 'min:3', 'max:150'],
            'guest_contact'         => ['required', 'string', 'max:150'],
            'guest_instansi'        => ['nullable', 'string', 'max:150'],
            'website'               => ['nullable', 'string', new HoneypotEmpty()],
            'cf-turnstile-response' => ['required', new ValidTurnstile()],
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required'               => 'Ruangan wajib dipilih.',
            'room_id.exists'                 => 'Ruangan tidak ditemukan.',
            'title.required'                 => 'Judul keperluan wajib diisi.',
            'start_datetime.after'           => 'Tanggal mulai harus di masa depan.',
            'end_datetime.after'             => 'Tanggal selesai harus setelah tanggal mulai.',
            'guest_name.required'            => 'Nama wajib diisi.',
            'guest_contact.required'         => 'Kontak (WhatsApp/Email) wajib diisi.',
            'cf-turnstile-response.required' => 'Verifikasi keamanan wajib diselesaikan.',
        ];
    }
}
