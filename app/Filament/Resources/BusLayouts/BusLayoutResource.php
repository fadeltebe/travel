<?php

namespace App\Filament\Resources\BusLayouts;

use App\Filament\Resources\BusLayouts\Pages\CreateBusLayout;
use App\Filament\Resources\BusLayouts\Pages\EditBusLayout;
use App\Filament\Resources\BusLayouts\Pages\ListBusLayouts;
use App\Filament\Resources\BusLayouts\Pages\ViewBusLayout;
use App\Filament\Resources\BusLayouts\Schemas\BusLayoutForm;
use App\Filament\Resources\BusLayouts\Schemas\BusLayoutInfolist;
use App\Filament\Resources\BusLayouts\Tables\BusLayoutsTable;
use App\Models\BusLayout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusLayoutResource extends Resource
{
    protected static ?string $model = BusLayout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BusLayoutForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BusLayoutInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusLayoutsTable::configure($table);
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
            'index' => ListBusLayouts::route('/'),
            'create' => CreateBusLayout::route('/create'),
            'view' => ViewBusLayout::route('/{record}'),
            'edit' => EditBusLayout::route('/{record}/edit'),
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
