<?php

namespace App\Filament\Resources\Cargos;

use App\Filament\Resources\Cargos\Pages\CreateCargo;
use App\Filament\Resources\Cargos\Pages\EditCargo;
use App\Filament\Resources\Cargos\Pages\ListCargos;
use App\Filament\Resources\Cargos\Schemas\CargoForm;
use App\Filament\Resources\Cargos\Tables\CargosTable;
use App\Models\Cargo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CargoResource extends Resource
{
    protected static ?string $model = Cargo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CargoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CargosTable::configure($table);
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
            'index' => ListCargos::route('/'),
            'create' => CreateCargo::route('/create'),
            'edit' => EditCargo::route('/{record}/edit'),
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
