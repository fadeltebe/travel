<?php

namespace App\Filament\SuperAdmin\Resources\Admins\Schemas;

use Illuminate\Support\Facades\DB;
use App\Models\Agen;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class AdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('user_name')
                ->label('Nama User')
                ->required()
                ->afterStateHydrated(fn($set, $record) => $set('user_name', $record?->user?->name)),

            TextInput::make('user_email')
                ->label('Email User')
                ->email()
                ->unique(
                    table: User::class,
                    column: 'email',
                    ignorable: fn($record) => $record?->user
                )
                ->required()
                ->afterStateHydrated(fn($set, $record) => $set('user_email', $record?->user?->email)),

            TextInput::make('user_password')
                ->label('Password User')
                ->password()
                ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null) // hanya hash jika diisi
                ->dehydrated(fn($state) => filled($state)) // hanya simpan kalau diisi
                ->required(fn(string $operation): bool => $operation === 'create'), // wajib hanya saat create

            Select::make('agen_id')
                ->label('Agen')
                ->options(fn() => Agen::pluck('name', 'id'))
                ->searchable()
                ->required()
                ->createOptionForm([
                    TextInput::make('name')->label('Nama Agen')->required(),
                    TextInput::make('alamat')->label('Alamat')->required(),
                    TextInput::make('nomor_hp')->label('Nomor HP')->required(),
                ]),

            TextInput::make('nik')
                ->label('NIK')
                ->nullable(),

            TextInput::make('nomor_hp')
                ->label('Nomor HP Admin')
                ->required(),

            Textarea::make('alamat')
                ->label('Alamat Admin')
                ->nullable(),

            Select::make('status')
                ->label('Status')
                ->options(['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'])
                ->default('aktif')
                ->required(),
        ]);
    }
}
