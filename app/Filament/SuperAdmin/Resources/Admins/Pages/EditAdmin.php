<?php

namespace App\Filament\Owner\Resources\Admins\Pages;

use App\Models\User;
use App\Services\UserService;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Owner\Resources\Admins\AdminResource;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    public function __construct()
    {
        $this->userService = app(UserService::class); // inject service manual
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * Menangani proses update record Admin + User
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Update user via service
            $this->userService->updateUser($this->record->user, [
                'name'     => $data['user_name'],
                'email'    => $data['user_email'],
                'password' => $data['user_password'] ?? null, // service akan handle hash & kosong
            ], $data['agen_id']);

            // Update relasi agen_user
            if (! empty($data['agen_id'])) {
                $this->record->user->agens()->sync([$data['agen_id']]);
            }

            // Update data Admin
            $this->record->update([
                'agen_id'  => $data['agen_id'],
                'nama'     => $data['user_name'],
                'nik'      => $data['nik'] ?? null,
                'nomor_hp' => $data['nomor_hp'],
                'alamat'   => $data['alamat'],
                'status'   => $data['status'],
            ]);

            return $this->record;
        });
    }
}
