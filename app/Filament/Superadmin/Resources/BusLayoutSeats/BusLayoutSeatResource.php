<?php

namespace App\Filament\Superadmin\Resources\BusLayoutSeats;

use App\Filament\Superadmin\Resources\BusLayoutSeats\Pages\CreateBusLayoutSeat;
use App\Filament\Superadmin\Resources\BusLayoutSeats\Pages\EditBusLayoutSeat;
use App\Filament\Superadmin\Resources\BusLayoutSeats\Pages\ListBusLayoutSeats;
use App\Filament\Superadmin\Resources\BusLayoutSeats\Schemas\BusLayoutSeatForm;
use App\Filament\Superadmin\Resources\BusLayoutSeats\Tables\BusLayoutSeatsTable;
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
