<?php

namespace App\Filament\App\Resources\TenantUserResource\Pages;

use App\Filament\App\Resources\TenantUserResource;
use App\Support\Tenancy\StaffInvite;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTenantUsers extends ListRecords
{
    protected static string $resource = TenantUserResource::class;

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos'),
            'pendientes' => Tab::make('Pendientes de aprobación')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => \App\Models\TenantUser::query()->where('status', 'pending')->count() ?: null)
                ->badgeColor('warning'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('inviteLink')
                ->label('Enlace de registro')
                ->icon('heroicon-o-link')
                ->color('gray')
                ->visible(fn (): bool => TenantUserResource::canCreate())
                ->modalHeading('Enlace para que el personal cree su cuenta')
                ->modalDescription('Compártelo por WhatsApp. Quien se registre quedará pendiente hasta que lo apruebes y le asignes un rol.')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar')
                ->fillForm(fn (): array => ['url' => StaffInvite::url(Filament::getTenant())])
                ->schema([
                    TextInput::make('url')->label('Enlace')->readOnly()->copyable(),
                ])
                ->extraModalFooterActions([
                    Action::make('regenerate')
                        ->label('Generar enlace nuevo')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('El enlace anterior dejará de funcionar.')
                        ->action(function (): void {
                            StaffInvite::regenerate(Filament::getTenant());
                            Notification::make()->title('Enlace regenerado. Ábrelo de nuevo para ver el nuevo.')->success()->send();
                        }),
                ]),
            CreateAction::make()->label('Agregar al personal'),
        ];
    }
}
