<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\TreatmentCatalogResource\Pages;
use App\Models\TreatmentCatalog;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TreatmentCatalogResource extends Resource
{
    protected static ?string $model = TreatmentCatalog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Tratamientos';

    protected static ?string $modelLabel = 'tratamiento';

    protected static ?string $pluralModelLabel = 'catálogo de tratamientos';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(150)
                ->columnSpanFull(),

            TextInput::make('category')
                ->label('Categoría')
                ->maxLength(60)
                ->helperText('Ej. Preventivo, Restaurativo, Cirugía, Ortodoncia.'),

            TextInput::make('base_price')
                ->label('Precio base')
                ->numeric()
                ->required()
                ->prefix('$'),

            TextInput::make('estimated_duration_minutes')
                ->label('Duración estimada (min)')
                ->numeric(),

            Toggle::make('requires_tooth')
                ->label('Requiere especificar diente')
                ->default(true)
                ->helperText('Desactívalo para procedimientos generales, ej. limpieza, consulta.'),

            Toggle::make('is_active')
                ->label('Activo')
                ->default(true),

            Textarea::make('description')
                ->label('Descripción')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge(),

                TextColumn::make('base_price')
                    ->label('Precio base')
                    ->money('usd')
                    ->sortable(),

                TextColumn::make('estimated_duration_minutes')
                    ->label('Duración')
                    ->suffix(' min'),

                IconColumn::make('requires_tooth')
                    ->label('Requiere diente')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTreatmentCatalogs::route('/'),
            'create' => Pages\CreateTreatmentCatalog::route('/create'),
            'edit'   => Pages\EditTreatmentCatalog::route('/{record}/edit'),
        ];
    }
}
