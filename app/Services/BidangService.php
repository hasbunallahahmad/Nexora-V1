<?php

namespace App\Services;

use App\Models\Bidang;
use App\Services\Base\BaseService;
use Illuminate\Support\Str;

class BidangService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new Bidang());
    }

    public function getValidationRules(?int $id = null): array
    {
        return [
            'nama_bidang' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z\s\&\-\.\,]+$/',
                'unique:bidang,nama_bidang,' . $id,
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                'unique:bidang,slug,' . $id,
            ],
        ];
    }

    public function getValidationAttributes(): array
    {
        return [
            'nama_bidang' => 'Nama Bidang',
            'slug' => 'slug',
        ];
    }

    public function sanitize(array $data): array
    {
        if (isset($data['nama_bidang'])) {
            $data['nama_bidang'] = trim($data['nama_bidang']);
            $data['nama_bidang'] = preg_replace('/\s+/', ' ', $data['nama_bidang']);
            $data['nama_bidang'] = ucwords(strtolower($data['nama_bidang']));
            $data['nama_bidang'] = strip_tags($data['nama_bidang']);
        }

        if (empty($data['slug']) && !empty($data['nama_bidang'])) {
            $data['slug'] = Str::slug($data['nama_bidang']);
        }

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        return $data;
    }

    public function findBySlug(string $slug): ?Bidang
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function search(string $keyword, int $perPage = 15)
    {
        return $this->model->search($keyword)->paginate($perPage);
    }
}
