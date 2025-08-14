<?php

namespace App\Filament\Admin\Resources\Jadwals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class JadwalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_jadwal')
                    ->searchable(),
                TextColumn::make('rute_display')
                    ->label('Rute')
                    ->getStateUsing(fn($record) => "{$record->rute->kota_asal} → {$record->rute->kota_tujuan}")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mobil.nomor_polisi')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('jam_berangkat')
                    ->time()
                    ->sortable(),
                TextColumn::make('jam_tiba_estimasi')
                    ->time()
                    ->sortable(),
                TextColumn::make('harga')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('kursi_tersedia')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
