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
            'guest_contact'         => ['required', 'digits_between:9,15'],
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
            'guest_contact.required'         => 'Nomor WhatsApp wajib diisi.',
            'guest_contact.digits_between'   => 'Nomor WhatsApp harus berupa angka saja (9-15 digit), tanpa spasi, kode negara +62, atau simbol lain.',
            'cf-turnstile-response.required' => 'Verifikasi keamanan wajib diselesaikan.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('guest_contact')) {
            $contact = preg_replace('/[^\d]/', '', (string) $this->input('guest_contact'));
            // Normalisasi: kalau diawali 62, ganti jadi 0
            if (str_starts_with($contact, '62')) {
                $contact = '0' . substr($contact, 2);
            }
            $this->merge(['guest_contact' => $contact]);
        }
    }
}
