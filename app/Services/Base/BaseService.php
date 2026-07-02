<?php

namespace App\Services\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseService
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    abstract public function getValidationRules(?int $id = null): array;

    public function getValidationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute ini sudah digunakan.',
            'min' => ':attribute minimal :min karakter.',
            'max' => ':attribute maksimal :max karakter.',
            'regex' => 'Format :attribute tidak valid.',
            'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, dash (-) dan underscore (_)',
        ];
    }

    public function sanitize(array $data): array
    {
        return $data;
    }

    public function validate(array $data, ?int $id = null): array
    {
        $validator = Validator::make(
            $data,
            $this->getValidationRules($id),
            $this->getValidationMessages(),
            $this->getValidationAttributes()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function getValidationAttributes(): array
    {
        return [];
    }

    public function create(array $data): Model
    {
        $validated = $this->validate($data);
        $sanitized = $this->sanitize($validated);

        return $this->model->create($sanitized);
    }

    public function update(int $id, array $data): Model
    {
        $record = $this->model->findorFail($id);
        $validated = $this->validate($data, $id);
        $sanitized = $this->sanitize($validated);

        $record->update($sanitized);

        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
