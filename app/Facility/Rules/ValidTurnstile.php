<?php

declare(strict_types=1);

namespace App\Facility\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

final class ValidTurnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('Verifikasi keamanan wajib diselesaikan.');
            return;
        }

        $response = Http::asForm()->post(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            [
                'secret'   => config('services.turnstile.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]
        );

        if (! $response->successful() || $response->json('success') !== true) {
            $fail('Verifikasi keamanan gagal, silakan coba lagi.');
        }
    }
}
