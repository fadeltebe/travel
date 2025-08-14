<?php

namespace App\Filament\SuperAdmin\Resources\Mobils;

use App\Filament\SuperAdmin\Resources\Mobils\Pages\CreateMobil;
use App\Filament\SuperAdmin\Resources\Mobils\Pages\EditMobil;
use App\Filament\SuperAdmin\Resources\Mobils\Pages\ListMobils;
use App\Filament\SuperAdmin\Resources\Mobils\Schemas\MobilForm;
use App\Filament\SuperAdmin\Resources\Mobils\Tables\MobilsTable;
use App\Models\Mobil;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MobilResource extends Resource
{
    protected static ?string $model = Mobil::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Mobil';

    public static function form(Schema $schema): Schema
    {
        return MobilForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MobilsTable::configure($table);
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
            'index' => ListMobils::route('/'),
            'create' => CreateMobil::route('/create'),
            'edit' => EditMobil::route('/{record}/edit'),
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
