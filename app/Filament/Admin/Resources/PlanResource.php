<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Facturación SaaS';

    protected static ?string $modelLabel = 'plan';

    protected static ?string $pluralModelLabel = 'planes';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(100),

            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true)
                ->helperText('Identificador interno, ej. "profesional-mensual".'),

            TextInput::make('price')
                ->label('Precio')
                ->numeric()
                ->required()
                ->prefix('$'),

            Select::make('billing_cycle')
                ->label('Ciclo de facturación')
                ->options([
                    'monthly' => 'Mensual',
                    'yearly'  => 'Anual',
                ])
                ->required()
                ->default('monthly'),

            TextInput::make('max_users')
                ->label('Máx. usuarios')
                ->numeric()
                ->helperText('Vacío = sin límite.'),

            TextInput::make('max_patients')
                ->label('Máx. pacientes')
                ->numeric()
                ->helperText('Vacío = sin límite.'),

            KeyValue::make('features')
                ->label('Funcionalidades')
                ->keyLabel('Clave')
                ->valueLabel('Valor')
                ->helperText('Flags de funcionalidades activas en este plan.')
                ->columnSpanFull(),

            Toggle::make('is_active')
                ->label('Activo')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('price')->label('Precio')->money('usd')->sortable(),
                TextColumn::make('billing_cycle')
                    ->label('Ciclo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'yearly' ? 'Anual' : 'Mensual'),
                TextColumn::make('tenants_count')->label('Clínicas')->counts('tenants')->sortable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit'   => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
