<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgendaCalendarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $bidangPertama = $this->resource->bidang->first();

        if (!$this->resource->start_date) {
            return [];
        }

        return [
            'id'              => $this->resource->id,
            'title'           => $this->resource->judul_agenda,
            'start'           => $this->resource->start_date->toDateString(),
            'end'             => $this->resource->end_date?->copy()->addDay()->toDateString(),
            'backgroundColor' => self::colorForBidang($bidangPertama),
            'borderColor'     => 'transparent',
            'textColor'       => '#ffffff',
            'extendedProps'   => [
                'waktu_mulai'   => $this->resource->waktu_mulai,
                'waktu_selesai' => $this->resource->waktu_selesai,
                'lokasi'        => $this->resource->location,
                'bidang'        => $this->resource->bidang->map(fn($b) => [
                    'id'   => $b->id,
                    'nama' => $b->nama_bidang,
                ])->values(),
                'start_format'  => $this->resource->start_format,
                'end_format'    => $this->resource->end_format,
                'slug'          => $this->resource->slug,
                'deskripsi'     => $this->resource->deskripsi,
            ],
        ];
    }

    public static function colorForBidang(?object $bidang): string
    {
        $palette = [
            '#0f1f3d',
            '#2980b9',
            '#27ae60',
            '#8e44ad',
            '#c0392b',
            '#16a085',
            '#d35400',
            '#2c3e50',
        ];

        if (! $bidang) return $palette[0];

        return $palette[$bidang->id % count($palette)];
    }
}
