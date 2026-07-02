<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PexelsHelper
{
    public static function getDailyImage(string $query = 'corporate building exterior'): string
    {
        $queries = [
            'government building architecture',
            'modern office interior',
            'corporate building exterior',
            'parliament building',
            'city hall building',
            'formal office workspace',
        ];

        $query = $queries[now()->dayOfYear % count($queries)];

        $cacheKey = 'pexels_daily_' . now()->format('Y-m-d');

        return Cache::remember($cacheKey, now()->endOfDay(), function () use ($query) {
            $response = Http::withHeaders([
                'Authorization' => config('services.pexels.key'),
            ])->get('https://api.pexels.com/v1/search', [
                'query'       => $query,
                'orientation' => 'landscape',
                'size'        => 'large',
                'per_page'    => 30,
                'page'        => now()->dayOfYear % 10 + 1, // ganti halaman tiap hari
            ]);

            if ($response->successful()) {
                $photos = $response->json('photos');
                // pilih foto berdasarkan hari
                $index = now()->dayOfYear % count($photos);
                return $photos[$index]['src']['original'] . '?auto=compress&w=1920';
            }

            // fallback jika API gagal
            return 'https://picsum.photos/seed/' . now()->format('Y-m-d') . '/1920/1080';
        });
    }
}
