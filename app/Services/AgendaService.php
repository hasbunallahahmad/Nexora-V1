<?php

namespace App\Services;

use App\Models\Agenda;
use App\Services\Base\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AgendaService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new Agenda());
    }

    public function getValidationRules(?int $id = null): array
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
        ];
    }

    public function getValidationAttributes(): array
    {
        return [
            'judul_agenda' => 'Judul Agenda',
            'deskripsi' => 'Deskripsi',
            'location' => 'Lokasi',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'is_published' => 'Status Publikasi',
        ];
    }

    public function getValidationMessages(): array
    {
        return array_merge(
            parent::getValidationMessages(),
            [
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
            ]
        );
    }

    public function sanitize(array $data): array
    {
        // Sanitize judul_agenda
        if (isset($data['judul_agenda'])) {
            $data['judul_agenda'] = trim($data['judul_agenda']);
            $data['judul_agenda'] = preg_replace('/\s+/', ' ', $data['judul_agenda']);
            $data['judul_agenda'] = strip_tags($data['judul_agenda']);
        }

        // Sanitize deskripsi
        if (isset($data['deskripsi'])) {
            $data['deskripsi'] = trim($data['deskripsi']);
            $data['deskripsi'] = preg_replace('/\s+/', ' ', $data['deskripsi']);
            $data['deskripsi'] = strip_tags($data['deskripsi']);
        }

        // Sanitize location
        if (isset($data['location'])) {
            $data['location'] = trim($data['location']);
            $data['location'] = preg_replace('/\s+/', ' ', $data['location']);
            $data['location'] = strip_tags($data['location']);
        }

        // Auto-generate slug if not provided
        if (empty($data['slug']) && !empty($data['judul_agenda'])) {
            $data['slug'] = Str::slug($data['judul_agenda']);
        }

        // Parse dates to ensure proper format
        if (isset($data['start_date']) && !$data['start_date'] instanceof Carbon) {
            $data['start_date'] = Carbon::parse($data['start_date']);
        }

        if (isset($data['end_date']) && $data['end_date'] && !$data['end_date'] instanceof Carbon) {
            $data['end_date'] = Carbon::parse($data['end_date']);
        }

        // Ensure is_published is boolean
        if (isset($data['is_published'])) {
            $data['is_published'] = (bool) $data['is_published'];
        }

        return $data;
    }

    public function findBySlug(string $slug): ?Agenda
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function search(string $keyword, int $perPage = 15)
    {
        return $this->model
            ->where('judul_agenda', 'like', "%{$keyword}%")
            ->orWhere('deskripsi', 'like', "%{$keyword}%")
            ->orWhere('location', 'like', "%{$keyword}%")
            ->paginate($perPage);
    }
}
