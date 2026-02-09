<?php

namespace App\Filament\Resources\BusLayoutSeats;

use App\Filament\Resources\BusLayoutSeats\Pages\CreateBusLayoutSeat;
use App\Filament\Resources\BusLayoutSeats\Pages\EditBusLayoutSeat;
use App\Filament\Resources\BusLayoutSeats\Pages\ListBusLayoutSeats;
use App\Filament\Resources\BusLayoutSeats\Pages\ViewBusLayoutSeat;
use App\Filament\Resources\BusLayoutSeats\Schemas\BusLayoutSeatForm;
use App\Filament\Resources\BusLayoutSeats\Schemas\BusLayoutSeatInfolist;
use App\Filament\Resources\BusLayoutSeats\Tables\BusLayoutSeatsTable;
use App\Models\BusLayoutSeat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusLayoutSeatResource extends Resource
{
    protected static ?string $model = BusLayoutSeat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BusLayoutSeatForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BusLayoutSeatInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusLayoutSeatsTable::configure($table);
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
            'index' => ListBusLayoutSeats::route('/'),
            'create' => CreateBusLayoutSeat::route('/create'),
            'view' => ViewBusLayoutSeat::route('/{record}'),
            'edit' => EditBusLayoutSeat::route('/{record}/edit'),
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
