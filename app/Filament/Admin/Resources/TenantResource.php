<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TenantResource\Pages;
use App\Filament\Admin\Resources\TenantResource\RelationManagers;
use App\Models\Tenant;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Plataforma';

    protected static ?string $modelLabel = 'clínica';

    protected static ?string $pluralModelLabel = 'clínicas';

    protected static ?int $navigationSort = 1;

    private const STATUSES = [
        'trial'     => 'Prueba',
        'active'    => 'Activa',
        'suspended' => 'Suspendida',
        'cancelled' => 'Cancelada',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('business_name')
                ->label('Nombre comercial')
                ->required()
                ->maxLength(150),

            TextInput::make('tax_id')
                ->label('RUC / NIT')
                ->maxLength(30),

            Select::make('plan_id')
                ->label('Plan')
                ->relationship('plan', 'name')
                ->searchable()
                ->preload(),

            Select::make('status')
                ->label('Estado')
                ->options(self::STATUSES)
                ->required()
                ->default('trial'),

            TextInput::make('address')
                ->label('Dirección')
                ->maxLength(255),

            TextInput::make('phone')
                ->label('Teléfono')
                ->tel()
                ->maxLength(30),

            TextInput::make('logo_url')
                ->label('URL del logo')
                ->url()
                ->maxLength(255),

            TextInput::make('timezone')
                ->label('Zona horaria')
                ->default('America/La_Paz')
                ->required(),

            TextInput::make('currency')
                ->label('Moneda')
                ->default('BOB')
                ->required()
                ->maxLength(10),

            KeyValue::make('cancellation_policy')
                ->label('Política de cancelación')
                ->columnSpanFull(),

            KeyValue::make('settings')
                ->label('Configuración')
                ->helperText('Ajustes propios de esta clínica.')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')->label('Clínica')->searchable()->sortable(),
                TextColumn::make('plan.name')->label('Plan')->badge()->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'trial'     => 'info',
                        'suspended' => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),
                TextColumn::make('currency')->label('Moneda')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Creada')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(self::STATUSES),
                SelectFilter::make('plan_id')->label('Plan')->relationship('plan', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('business_name');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StaffRelationManager::class,
            RelationManagers\SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit'   => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
