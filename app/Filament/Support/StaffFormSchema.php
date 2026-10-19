<?php

namespace App\Filament\Support;

use App\Models\Role;
use App\Models\User;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Campos del formulario de personal, compartidos entre el panel app
 * (TenantUserResource) y el panel admin (StaffRelationManager).
 */
class StaffFormSchema
{
    /**
     * @param  Closure(): (int|string|null)  $tenantId  clínica sobre la que se listan los roles
     */
    public static function components(Closure $tenantId): array
    {
        return [
            TextInput::make('email')
                ->label('Correo')
                ->email()
                ->required()
                ->maxLength(150)
                ->live(onBlur: true)
                ->visibleOn('create')
                ->helperText('Si ya tiene cuenta en Orbis se vincula a esta clínica; si no, se crea.'),

            TextInput::make('name')
                ->label('Nombre')
                ->maxLength(120)
                ->visibleOn('create')
                ->required(fn (Get $get): bool => filled($get('email'))
                    && ! User::query()->where('email', strtolower(trim((string) $get('email'))))->exists())
                ->helperText('Solo se usa si el correo todavía no tiene cuenta.'),

            TextInput::make('user_email')
                ->label('Persona')
                ->disabled()
                ->dehydrated(false)
                ->visibleOn('edit')
                ->formatStateUsing(fn ($record) => $record?->user?->email),

            Select::make('role_id')
                ->label('Rol')
                ->options(fn () => Role::query()
                    ->availableFor($tenantId())
                    ->orderBy('name')
                    ->pluck('name', 'id'))
                ->required()
                ->searchable(),

            Select::make('status')
                ->label('Estado')
                ->options([
                    'active'   => 'Activo',
                    'pending'  => 'Pendiente de aprobación',
                    'invited'  => 'Invitado (sin acceso aún)',
                    'disabled' => 'Deshabilitado',
                ])
                ->required()
                ->default('active'),
        ];
    }
}
