<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class KalenderFeedRequest extends FormRequest
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
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
        ];
    }

    public function parsed(): array
    {
        return [
            'start' => Carbon::parse($this->input('start'))->startOfDay(),
            'end' => Carbon::parse($this->input('end'))->endOfDay(),
        ];
    }

    public function messages(): array
    {
        return [
            'start.required'      => 'Parameter start wajib diisi.',
            'start.date_format'   => 'Format start harus YYYY-MM-DD.',
            'end.required'        => 'Parameter end wajib diisi.',
            'end.date_format'     => 'Format end harus YYYY-MM-DD.',
            'end.after_or_equal'  => 'Tanggal akhir tidak boleh sebelum tanggal mulai.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $start = Carbon::parse($this->input('start'));
            $end   = Carbon::parse($this->input('end'));

            if ($start->diffInDays($end) > 93) {
                $v->errors()->add('end', 'Rentang tanggal maksimal 93 hari.');
            }
        });
    }
}
