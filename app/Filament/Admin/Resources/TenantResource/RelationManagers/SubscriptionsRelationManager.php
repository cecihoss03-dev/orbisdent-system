<?php

namespace App\Filament\Admin\Resources\TenantResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $title = 'Historial de suscripción';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('plan_id')
                ->label('Plan')
                ->relationship('plan', 'name')
                ->required(),

            DatePicker::make('start_date')->label('Inicio')->required()->native(false),

            DatePicker::make('end_date')->label('Fin')->native(false),

            Select::make('status')
                ->label('Estado')
                ->options([
                    'active'    => 'Activa',
                    'expired'   => 'Expirada',
                    'cancelled' => 'Cancelada',
                ])
                ->required()
                ->default('active'),

            TextInput::make('amount_paid')->label('Monto pagado')->numeric()->prefix('$'),

            TextInput::make('payment_method')->label('Método de pago'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('plan.name')->label('Plan'),
                TextColumn::make('start_date')->label('Inicio')->date(),
                TextColumn::make('end_date')->label('Fin')->date(),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('amount_paid')->label('Monto')->money('usd'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('start_date', 'desc');
    }
}
