<?php

namespace App\Filament\Resources\Passengers;

use App\Filament\Resources\Passengers\Pages\CreatePassenger;
use App\Filament\Resources\Passengers\Pages\EditPassenger;
use App\Filament\Resources\Passengers\Pages\ListPassengers;
use App\Filament\Resources\Passengers\Schemas\PassengerForm;
use App\Filament\Resources\Passengers\Tables\PassengersTable;
use App\Models\Passenger;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PassengerResource extends Resource
{
    protected static ?string $model = Passenger::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PassengerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PassengersTable::configure($table);
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
            'index' => ListPassengers::route('/'),
            'create' => CreatePassenger::route('/create'),
            'edit' => EditPassenger::route('/{record}/edit'),
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
