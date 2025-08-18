<?php

namespace App\Filament\Driver\Resources\Jadwals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\MoneyColumn;

class JadwalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_jadwal')->label('Kode Jadwal')->searchable(),
                TextColumn::make('rute.kota_asal')->label('Asal'),
                TextColumn::make('rute.kota_tujuan')->label('Tujuan'),
                TextColumn::make('tanggal')->label('Tanggal')->date(),
                TextColumn::make('jam_berangkat')->label('Jam Berangkat'),
                TextColumn::make('jam_tiba_estimasi')->label('Jam Tiba Estimasi'),
                BadgeColumn::make('status')->label('Status'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            // ->toolbarActions([
            //     BulkActionGroup::make([
            //         DeleteBulkAction::make(),
            //         ForceDeleteBulkAction::make(),
            //         RestoreBulkAction::make(),
            //     ]),
            // ])
        ;
    }
}
