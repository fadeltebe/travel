<?php

namespace App\Filament\SuperAdmin\Resources\Admins;


use BackedEnum;
use App\Models\Admin;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\SuperAdmin\Resources\Admins\Pages\EditAdmin;
use App\Filament\SuperAdmin\Resources\Admins\Pages\ViewAdmin;
use App\Filament\SuperAdmin\Resources\Admins\Pages\ListAdmins;
use App\Filament\SuperAdmin\Resources\Admins\Pages\CreateAdmin;
use App\Filament\SuperAdmin\Resources\Admins\Schemas\AdminForm;
use App\Filament\SuperAdmin\Resources\Admins\Tables\AdminsTable;
use App\Filament\SuperAdmin\Resources\Admins\Schemas\AdminInfolist;

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
