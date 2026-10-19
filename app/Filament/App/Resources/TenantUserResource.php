<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\TenantUserResource\Pages;
use App\Filament\Support\StaffFormSchema;
use App\Models\TenantUser;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use App\Models\Role;
use Filament\Notifications\Notification;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TenantUserResource extends Resource
{
    protected static ?string $model = TenantUser::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Personal';

    protected static ?string $modelLabel = 'miembro del personal';

    protected static ?string $pluralModelLabel = 'personal';

    protected static ?int $navigationSort = 1;

    // tenant_users sí tiene tenant_id obligatorio y relación tenant(): el
    // scoping automático de tenancy de Filament funciona sin ayuda.

    public static function form(Schema $schema): Schema
    {
        return $schema->components(
            StaffFormSchema::components(fn () => Filament::getTenant()?->getKey())
        )->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Nombre')->searchable(),
                TextColumn::make('user.email')->label('Correo')->searchable(),
                TextColumn::make('role.name')->label('Rol')->badge()->placeholder('Sin rol'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'pending'  => 'warning',
                        'invited'  => 'info',
                        'disabled' => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('joined_at')->label('Desde')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active'   => 'Activo',
                        'pending'  => 'Pendiente',
                        'invited'  => 'Invitado',
                        'disabled' => 'Deshabilitado',
                    ]),
                SelectFilter::make('role_id')->label('Rol')->relationship('role', 'name'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === 'pending'
                        && (auth()->user()?->can('update', $record) ?? false))
                    ->modalHeading('Aprobar y asignar rol')
                    ->schema([
                        Select::make('role_id')
                            ->label('Rol')
                            ->options(fn () => Role::query()
                                ->availableFor(Filament::getTenant()?->getKey())
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update(['role_id' => $data['role_id'], 'status' => 'active']);
                        Notification::make()->title('Persona aprobada')->success()->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('joined_at', 'desc');
    }

    public static function getNavigationBadge(): ?string
    {
        $n = TenantUser::query()->where('status', 'pending')->count();

        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTenantUsers::route('/'),
            'create' => Pages\CreateTenantUser::route('/create'),
            'edit'   => Pages\EditTenantUser::route('/{record}/edit'),
        ];
    }
}
