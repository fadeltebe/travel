<?php

namespace App\Filament\Resources\Travel;

use App\Filament\Resources\Travel\Pages\CreateTravel;
use App\Filament\Resources\Travel\Pages\EditTravel;
use App\Filament\Resources\Travel\Pages\ListTravel;
use App\Filament\Resources\Travel\Pages\ViewTravel;
use App\Filament\Resources\Travel\Schemas\TravelForm;
use App\Filament\Resources\Travel\Schemas\TravelInfolist;
use App\Filament\Resources\Travel\Tables\TravelTable;
use App\Models\Travel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TravelResource extends Resource
{
    protected static ?string $model = Travel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Travel';

    public static function form(Schema $schema): Schema
    {
        return TravelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TravelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TravelTable::configure($table);
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
            'index' => ListTravel::route('/'),
            'create' => CreateTravel::route('/create'),
            'view' => ViewTravel::route('/{record}'),
            'edit' => EditTravel::route('/{record}/edit'),
        ];
    }
}
