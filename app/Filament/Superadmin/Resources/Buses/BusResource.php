<?php

namespace App\Filament\Superadmin\Resources\Buses;

use App\Filament\Superadmin\Resources\Buses\Pages\CreateBus;
use App\Filament\Superadmin\Resources\Buses\Pages\EditBus;
use App\Filament\Superadmin\Resources\Buses\Pages\ListBuses;
use App\Filament\Superadmin\Resources\Buses\Schemas\BusForm;
use App\Filament\Superadmin\Resources\Buses\Tables\BusesTable;
use App\Models\Bus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusResource extends Resource
{
    protected static ?string $model = Bus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusesTable::configure($table);
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
            'index' => ListBuses::route('/'),
            'create' => CreateBus::route('/create'),
            'edit' => EditBus::route('/{record}/edit'),
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
