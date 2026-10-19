<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\RoleResource\Pages;
use App\Models\Permission;
use App\Models\Role;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Personal';

    protected static ?string $modelLabel = 'rol';

    protected static ?string $pluralModelLabel = 'roles';

    protected static ?int $navigationSort = 2;

    /**
     * Role.tenant_id es nullable (NULL = rol de sistema). El scoping
     * automático de tenancy de Filament filtraría "tenant_id = esta
     * clínica" y ocultaría los roles de sistema, así que lo apagamos y
     * aplicamos Role::availableFor() a mano.
     */
    protected static bool $isScopedToTenant = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->availableFor(Filament::getTenant()?->getKey());
    }

    // Los roles de sistema se gestionan por seeder, no desde una clínica;
    // y un rol con personal asignado no se puede eliminar.
    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record) && ! $record->is_system;
    }

    public static function canDelete(Model $record): bool
    {
        return parent::canDelete($record) && ! $record->is_system && $record->tenantUsers()->doesntExist();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre del rol')
                ->required()
                ->maxLength(80),

            CheckboxList::make('permissions')
                ->label('Permisos')
                ->relationship('permissions', 'name')
                ->getOptionLabelFromRecordUsing(fn (Permission $record) => "{$record->module} — {$record->name}")
                ->columns(2)
                ->searchable()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                IconColumn::make('is_system')->label('Rol de sistema')->boolean(),
                TextColumn::make('permissions_count')->label('Permisos')->counts('permissions'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit'   => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
