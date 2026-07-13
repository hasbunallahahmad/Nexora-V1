<?php

declare(strict_types=1);

namespace App\Activity\Repositories;

use App\Activity\Models\Agenda;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class AgendaRepository
{
    public function findOrFail(int $id): Agenda
    {
        return Agenda::query()->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Agenda
    {
        return Agenda::query()->where('slug', $slug)->first();
    }

    public function findBySlugOrFail(string $slug): Agenda
    {
        return Agenda::query()->where('slug', $slug)->firstOrFail();
    }

    public function create(array $data): Agenda
    {
        return Agenda::create($data);
    }

    public function update(Agenda $agenda, array $data): Agenda
    {
        $agenda->update($data);

        return $agenda->fresh();
    }

    public function delete(Agenda $agenda): bool
    {
        return (bool) $agenda->delete();
    }

    public function syncBidang(Agenda $agenda, array $bidangIds): void
    {
        $agenda->bidang()->sync($bidangIds);
    }

    public function publishedHariIni(): Collection
    {
        return Agenda::published()->hariIni()->with('bidang')->get();
    }

    public function publishedMendatang(int $limit = 9): Collection
    {
        return Agenda::published()->mendatang()->with('bidang')->take($limit)->get();
    }

    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return Agenda::query()
            ->where('judul_agenda', 'like', "%{$keyword}%")
            ->orWhere('deskripsi', 'like', "%{$keyword}%")
            ->orWhere('location', 'like', "%{$keyword}%")
            ->paginate($perPage);
    }
}
