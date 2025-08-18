<?php

namespace App\Filament\Driver\Resources\Jadwals;

use BackedEnum;
use App\Models\Jadwal;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use App\Filament\Driver\Resources\Jadwals\Pages\EditJadwal;
use App\Filament\Driver\Resources\Jadwals\Pages\ListJadwals;
use App\Filament\Driver\Resources\Jadwals\Pages\CreateJadwal;
use App\Filament\Driver\Resources\Jadwals\Schemas\JadwalForm;
use App\Filament\Driver\Resources\Jadwals\Tables\JadwalsTable;

class JadwalResource extends Resource
{
    protected static ?string $model = Jadwal::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::CalendarDays;

    protected static ?string $recordTitleAttribute = 'Driver';

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

    /**
     * Batasi jadwal hanya untuk driver yang sedang login.
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $driver = $user?->driver; // pastikan relasi user->driver ada

        return parent::getEloquentQuery()
            ->when($driver, function ($query) use ($driver) {
                $query->whereHas('drivers', function ($q) use ($driver) {
                    $q->where('drivers.id', $driver->id);
                });
            });
    }
}
