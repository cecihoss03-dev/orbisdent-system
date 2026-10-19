<?php

namespace App\Filament\Admin\Resources\TenantResource\RelationManagers;

use App\Filament\Support\StaffFormSchema;
use App\Support\Tenancy\AddStaffMember;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Permite al Admin General dar de alta al primer usuario (dueño) de una
 * clínica recién creada, sin pasar por Tinker.
 */
class StaffRelationManager extends RelationManager
{
    protected static string $relationship = 'tenantUsers';

    protected static ?string $title = 'Personal de la clínica';

    public function form(Schema $schema): Schema
    {
        return $schema->components(
            StaffFormSchema::components(fn () => $this->getOwnerRecord()->getKey())
        )->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('user.name')->label('Nombre'),
                TextColumn::make('user.email')->label('Correo'),
                TextColumn::make('role.name')->label('Rol')->badge(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'invited'  => 'info',
                        'disabled' => 'danger',
                        default    => 'gray',
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar personal')
                    ->using(function (array $data, Action $action): Model {
                        try {
                            return AddStaffMember::handle($this->getOwnerRecord()->getKey(), $data);
                        } catch (DomainException $e) {
                            Notification::make()->title($e->getMessage())->danger()->send();
                            $action->halt();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
