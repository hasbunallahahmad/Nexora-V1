<?php
// App/Http/Resources/AgendaDetailResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgendaDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'judul'         => $this->judul_agenda,
            'slug'          => $this->slug,
            'deskripsi'     => $this->deskripsi,
            'start_date'    => $this->start_format,
            'waktu_mulai'   => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'lokasi'        => $this->location,
            'bidang'        => $this->bidang->map(fn($b) => [
                'id'   => $b->id,
                'nama' => $b->nama_bidang,
            ])->values(),
        ];
    }
}
