<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $modelLabel = 'proveedor';

    protected static ?string $pluralModelLabel = 'proveedores';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(150)
                ->columnSpanFull(),

            TextInput::make('contact')
                ->label('Persona de contacto')
                ->maxLength(120),

            TextInput::make('phone')
                ->label('Teléfono')
                ->tel()
                ->maxLength(30),

            TextInput::make('email')
                ->label('Correo')
                ->email()
                ->maxLength(150),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('contact')->label('Contacto'),
                TextColumn::make('phone')->label('Teléfono'),
                TextColumn::make('email')->label('Correo'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit'   => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
