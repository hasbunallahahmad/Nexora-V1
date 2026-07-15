<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Pengguna')
                ->modalWidth('lg')
                ->schema(UserForm::getComponents())
                ->using(function (array $data): Model {
                    $temporaryPassword = Str::password(12);

                    $roles = $data['roles'] ?? [];
                    unset($data['roles']);

                    $user = User::create([
                        ...$data,
                        'password'              => Hash::make($temporaryPassword),
                        'email_verified_at'     => now(),
                        'must_change_password'  => true,
                    ]);
                    $user->forceFill(['email_verified_at' => now()])->save();
                    if (! empty($roles)) {
                        $user->roles()->sync($roles);
                    }

                    Notification::make()
                        ->title('Pengguna berhasil dibuat')
                        ->body("Password sementara: {$temporaryPassword} — salin sekarang, tidak akan ditampilkan lagi.")
                        ->success()
                        ->persistent()
                        ->send();

                    return $user;
                }),
        ];
    }
}
