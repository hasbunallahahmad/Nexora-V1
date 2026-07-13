<?php

declare(strict_types=1);

namespace App\Activity\Services;

use App\Activity\Models\Agenda;
use App\Activity\Repositories\AgendaRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AgendaService
{
    public function __construct(
        private readonly AgendaRepository $agendas,
    ) {}

    public function create(array $data): Agenda
    {
        $validated = $this->validate($data);
        $sanitized = $this->sanitize($validated);

        $bidangIds = $sanitized['bidang_id'] ?? [];
        unset($sanitized['bidang_id']);

        $agenda = $this->agendas->create($sanitized);

        if (! empty($bidangIds)) {
            $this->agendas->syncBidang($agenda, $bidangIds);
        }

        return $agenda->fresh();
    }

    public function update(int $id, array $data): Agenda
    {
        $agenda = $this->agendas->findOrFail($id);

        $validated = $this->validate($data, $id);
        $sanitized = $this->sanitize($validated);

        $bidangIds = $sanitized['bidang_id'] ?? null;
        unset($sanitized['bidang_id']);

        $agenda = $this->agendas->update($agenda, $sanitized);

        if ($bidangIds !== null) {
            $this->agendas->syncBidang($agenda, $bidangIds);
        }

        return $agenda->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->agendas->delete($this->agendas->findOrFail($id));
    }

    public function findBySlug(string $slug): ?Agenda
    {
        return $this->agendas->findBySlug($slug);
    }

    public function search(string $keyword, int $perPage = 15)
    {
        return $this->agendas->search($keyword, $perPage);
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
            'deskripsi' => ['nullable', 'string', 'max:150'],
            'location' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\&\-\.\,\(\)]+$/u',
            ],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_published' => ['boolean'],
            'bidang_id' => ['nullable', 'array'],
            'bidang_id.*' => ['integer', 'exists:bidang,id'],
        ];
    }

    public function getValidationMessages(): array
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
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh lebih awal dari hari ini.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'bidang_id.array' => 'Bidang harus berupa array.',
            'bidang_id.*.exists' => 'Salah satu bidang tidak ditemukan.',
        ];
    }

    public function validate(array $data, ?int $id = null): array
    {
        $validator = Validator::make(
            $data,
            $this->getValidationRules($id),
            $this->getValidationMessages(),
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function sanitize(array $data): array
    {
        foreach (['judul_agenda', 'deskripsi', 'location'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim($data[$field]);
                $data[$field] = preg_replace('/\s+/', ' ', $data[$field]);
                $data[$field] = strip_tags($data[$field]);
            }
        }

        if (empty($data['slug']) && ! empty($data['judul_agenda'])) {
            $data['slug'] = Str::slug($data['judul_agenda']);
        }

        if (isset($data['start_date']) && ! $data['start_date'] instanceof Carbon) {
            $data['start_date'] = Carbon::parse($data['start_date']);
        }

        if (isset($data['end_date']) && $data['end_date'] && ! $data['end_date'] instanceof Carbon) {
            $data['end_date'] = Carbon::parse($data['end_date']);
        }

        if (isset($data['is_published'])) {
            $data['is_published'] = (bool) $data['is_published'];
        }

        return $data;
    }
}
