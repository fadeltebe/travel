<?php

namespace App\Filament\Owner\Resources\Agens;

use App\Filament\Owner\Resources\Agens\Pages\CreateAgen;
use App\Filament\Owner\Resources\Agens\Pages\EditAgen;
use App\Filament\Owner\Resources\Agens\Pages\ListAgens;
use App\Filament\Owner\Resources\Agens\Pages\ViewAgen;
use App\Filament\Owner\Resources\Agens\Schemas\AgenForm;
use App\Filament\Owner\Resources\Agens\Schemas\AgenInfolist;
use App\Filament\Owner\Resources\Agens\Tables\AgensTable;
use App\Models\Agen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AgenResource extends Resource
{
    protected static ?string $model = Agen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Agen';

    public static function form(Schema $schema): Schema
    {
        return AgenForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AgenInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgensTable::configure($table);
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
            'index' => ListAgens::route('/'),
            'create' => CreateAgen::route('/create'),
            'view' => ViewAgen::route('/{record}'),
            'edit' => EditAgen::route('/{record}/edit'),
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
