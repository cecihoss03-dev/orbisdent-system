<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ChairResource\Pages;
use App\Models\Chair;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChairResource extends Resource
{
    protected static ?string $model = Chair::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = 'Agenda';

    protected static ?string $modelLabel = 'sillón';

    protected static ?string $pluralModelLabel = 'sillones';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(60),

            Select::make('status')
                ->label('Estado')
                ->options([
                    'available'   => 'Disponible',
                    'occupied'    => 'Ocupado',
                    'maintenance' => 'En mantenimiento',
                ])
                ->required()
                ->default('available'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available'   => 'success',
                        'occupied'    => 'warning',
                        'maintenance' => 'danger',
                        default       => 'gray',
                    }),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListChairs::route('/'),
            'create' => Pages\CreateChair::route('/create'),
            'edit'   => Pages\EditChair::route('/{record}/edit'),
        ];
    }
}
