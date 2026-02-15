<?php

namespace App\Filament\Superadmin\Resources\CargoReceipts;

use App\Filament\Superadmin\Resources\CargoReceipts\Pages\CreateCargoReceipt;
use App\Filament\Superadmin\Resources\CargoReceipts\Pages\EditCargoReceipt;
use App\Filament\Superadmin\Resources\CargoReceipts\Pages\ListCargoReceipts;
use App\Filament\Superadmin\Resources\CargoReceipts\Schemas\CargoReceiptForm;
use App\Filament\Superadmin\Resources\CargoReceipts\Tables\CargoReceiptsTable;
use App\Models\CargoReceipt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CargoReceiptResource extends Resource
{
    protected static ?string $model = CargoReceipt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CargoReceiptForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CargoReceiptsTable::configure($table);
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
            'index' => ListCargoReceipts::route('/'),
            'create' => CreateCargoReceipt::route('/create'),
            'edit' => EditCargoReceipt::route('/{record}/edit'),
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
