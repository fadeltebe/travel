<?php

namespace App\Filament\SuperAdmin\Resources\Rutes;

use App\Filament\SuperAdmin\Resources\Rutes\Pages\CreateRute;
use App\Filament\SuperAdmin\Resources\Rutes\Pages\EditRute;
use App\Filament\SuperAdmin\Resources\Rutes\Pages\ListRutes;
use App\Filament\SuperAdmin\Resources\Rutes\Schemas\RuteForm;
use App\Filament\SuperAdmin\Resources\Rutes\Tables\RutesTable;
use App\Models\Rute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RuteResource extends Resource
{
    protected static ?string $model = Rute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Rute';

    public static function form(Schema $schema): Schema
    {
        return RuteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RutesTable::configure($table);
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
            'index' => ListRutes::route('/'),
            'create' => CreateRute::route('/create'),
            'edit' => EditRute::route('/{record}/edit'),
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
