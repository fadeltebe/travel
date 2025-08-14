<?php

namespace App\Filament\SuperAdmin\Resources\Admins\Pages;

use Illuminate\Support\Facades\DB;
use App\Models\Agen;
use App\Models\Admin;
use App\Services\UserService;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\SuperAdmin\Resources\Admins\AdminResource;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    public function __construct()
    {
        $this->userService = app(UserService::class); // inject service manual
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Admin
    {
        return DB::transaction(function () use ($data) {

            // 1. Buat User via Service
            $user = $this->userService->createUser([
                'name'     => $data['user_name'],
                'email'    => $data['user_email'],
                'password' => $data['user_password'],
            ], $data['agen_id']);

            // 2. Tambahkan ke agen_user
            $user->agens()->attach($data['agen_id']);

            // 3. Buat Admin
            return Admin::create([
                'user_id'  => $user->id,
                'agen_id'  => $data['agen_id'],
                'nama'     => $data['nama'],
                'nik'      => $data['nik'] ?? null,
                'nomor_hp' => $data['nomor_hp'],
                'alamat'   => $data['alamat'],
                'status'   => $data['status'],
            ]);
        });
    }
}
