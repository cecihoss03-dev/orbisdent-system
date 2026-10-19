<?php

namespace App\Filament\App\Resources\PatientResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PrescriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'prescriptions';

    protected static ?string $title = 'Recetas';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('notes')
                ->label('Notas generales')
                ->columnSpanFull(),

            Repeater::make('items')
                ->relationship()
                ->label('Medicamentos')
                ->schema([
                    TextInput::make('medication_name')
                        ->label('Medicamento')
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('dosage')
                        ->label('Dosis'),

                    TextInput::make('frequency')
                        ->label('Frecuencia'),

                    TextInput::make('duration')
                        ->label('Duración'),

                    Textarea::make('instructions')
                        ->label('Instrucciones')
                        ->columnSpanFull()
                        ->rows(2),
                ])
                ->columns(3)
                ->addActionLabel('Agregar medicamento')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('issued_at')
                    ->label('Emitida')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('dentist.name')
                    ->label('Odontólogo'),

                TextColumn::make('items_count')
                    ->label('Medicamentos')
                    ->counts('items'),
            ])
            ->defaultSort('issued_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['dentist_id'] = Auth::id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
