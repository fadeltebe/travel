<?php

namespace App\Filament\Owner\Resources\Admins;

use App\Filament\Owner\Resources\Admins\Pages\CreateAdmin;
use App\Filament\Owner\Resources\Admins\Pages\EditAdmin;
use App\Filament\Owner\Resources\Admins\Pages\ListAdmins;
use App\Filament\Owner\Resources\Admins\Pages\ViewAdmin;
use App\Filament\Owner\Resources\Admins\Schemas\AdminForm;
use App\Filament\Owner\Resources\Admins\Schemas\AdminInfolist;
use App\Filament\Owner\Resources\Admins\Tables\AdminsTable;
use App\Models\Admin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Admin';

    public static function form(Schema $schema): Schema
    {
        return AdminForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdminInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminsTable::configure($table);
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
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmin::route('/create'),
            'view' => ViewAdmin::route('/{record}'),
            'edit' => EditAdmin::route('/{record}/edit'),
        ];
    }
}
