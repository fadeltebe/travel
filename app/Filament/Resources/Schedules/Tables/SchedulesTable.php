<?php

namespace App\Filament\Resources\Schedules\Tables;

use Filament\Tables\Table;

use Illuminate\Support\Carbon;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;


class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([


                Split::make([

                    Stack::make([
                        // KOLOM 1: Route, Tanggal Berangkat, Tanggal Tiba
                        TextColumn::make('schedule_info')
                            ->label('Jadwal')
                            ->html()
                            ->getStateUsing(function ($record) {
                                // ✨ Null safety check yang lengkap
                                if (!$record->route) {
                                    return "<span class='text-gray-400'>Route tidak tersedia</span>";
                                }

                                if (!$record->route->originAgent || !$record->route->destinationAgent) {
                                    return "<span class='text-gray-400'>Data agent tidak lengkap</span>";
                                }

                                // Tampilkan nama agent
                                $route = "<strong class='text-primary-600'>{$record->route->originAgent->city} → {$record->route->destinationAgent->city}</strong>";

                                // Format tanggal berangkat dengan Carbon
                                $departureDateTime = \Carbon\Carbon::parse($record->departure_date)->format('d M Y') . ', ' .
                                    \Carbon\Carbon::parse($record->departure_time)->format('H:i');
                                $departure = "🚌 Berangkat: {$departureDateTime}";

                                // Format waktu tiba
                                $arrivalDateTime = \Carbon\Carbon::parse($record->arrival_date)->format('d M Y') . ', ' .
                                    \Carbon\Carbon::parse($record->arrival_time)->format('H:i');
                                $arrival = "🏁 Tiba: {$arrivalDateTime}";

                                return "{$route}<br><small class='text-gray-600'>{$departure}<br>{$arrival}</small>";
                            })
                            ->searchable(),
                    ]),
                    Stack::make([
                        // KOLOM 2: Bus, Harga, Kursi
                        TextColumn::make('bus_info')
                            ->label('Detail')
                            ->html()
                            ->getStateUsing(function ($record) {
                                $bus = "🚍 <strong>{$record->bus->plate_number}</strong>";
                                $price = "💰 Rp " . number_format($record->price, 0, ',', '.');
                                $seats = "🪑 {$record->available_seats} kursi tersedia";

                                return "{$bus}<br><small class='text-gray-600'>{$price} | {$seats}</small>";
                            })
                            ->searchable(['buses.plate_number', 'buses.name']),

                        // Badge Status dengan Filament native
                        TextColumn::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'scheduled' => 'info',
                                'departed' => 'warning',
                                'arrived' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'scheduled' => 'Terjadwal',
                                'departed' => 'Berangkat',
                                'arrived' => 'Tiba',
                                'cancelled' => 'Dibatalkan',
                                default => $state,
                            }),
                    ])
                ]),
            ])





            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
