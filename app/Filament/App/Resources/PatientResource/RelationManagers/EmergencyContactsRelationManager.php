<?php

namespace App\Filament\App\Resources\PatientResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmergencyContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'emergencyContacts';

    protected static ?string $title = 'Contactos de emergencia';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(120),

            TextInput::make('relationship')
                ->label('Parentesco')
                ->maxLength(60),

            TextInput::make('phone')
                ->label('Teléfono')
                ->tel()
                ->maxLength(30),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Nombre'),
                TextColumn::make('relationship')->label('Parentesco'),
                TextColumn::make('phone')->label('Teléfono'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
