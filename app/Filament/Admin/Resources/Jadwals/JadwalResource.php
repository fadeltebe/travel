<?php

namespace App\Filament\Admin\Resources\Jadwals;

use App\Filament\Admin\Resources\Jadwals\Pages\CreateJadwal;
use App\Filament\Admin\Resources\Jadwals\Pages\EditJadwal;
use App\Filament\Admin\Resources\Jadwals\Pages\ListJadwals;
use App\Filament\Admin\Resources\Jadwals\Schemas\JadwalForm;
use App\Filament\Admin\Resources\Jadwals\Tables\JadwalsTable;
use App\Models\Jadwal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;

class JadwalResource extends Resource
{
    protected static ?string $model = Jadwal::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::CalendarDays;

    protected static ?string $recordTitleAttribute = 'Jadwal';

    public static function form(Schema $schema): Schema
    {
        return JadwalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JadwalsTable::configure($table);
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
            'index' => ListJadwals::route('/'),
            'create' => CreateJadwal::route('/create'),
            'edit' => EditJadwal::route('/{record}/edit'),
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
