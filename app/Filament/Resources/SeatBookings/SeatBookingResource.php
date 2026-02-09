<?php

namespace App\Filament\Resources\SeatBookings;

use App\Filament\Resources\SeatBookings\Pages\CreateSeatBooking;
use App\Filament\Resources\SeatBookings\Pages\EditSeatBooking;
use App\Filament\Resources\SeatBookings\Pages\ListSeatBookings;
use App\Filament\Resources\SeatBookings\Schemas\SeatBookingForm;
use App\Filament\Resources\SeatBookings\Tables\SeatBookingsTable;
use App\Models\SeatBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SeatBookingResource extends Resource
{
    protected static ?string $model = SeatBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SeatBookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeatBookingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeatBookings::route('/'),
            'create' => CreateSeatBooking::route('/create'),
            'edit' => EditSeatBooking::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
