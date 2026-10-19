<?php

namespace App\Filament\App\Resources\PatientResource\RelationManagers;

use App\Models\Cie10DentalCatalog;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ClinicalEvolutionNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'clinicalEvolutionNotes';

    protected static ?string $title = 'Notas de evolución';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('visit_date')
                ->label('Fecha de la visita')
                ->required()
                ->default(now())
                ->native(false),

            TextInput::make('reason')
                ->label('Motivo de consulta')
                ->columnSpanFull(),

            Select::make('diagnosis_cie10')
                ->label('Diagnóstico (CIE-10)')
                ->options(fn () => Cie10DentalCatalog::query()->pluck('description', 'code'))
                ->searchable()
                ->columnSpanFull(),

            Textarea::make('subjective')
                ->label('S — Subjetivo')
                ->helperText('Lo que el paciente refiere.')
                ->rows(2),

            Textarea::make('objective')
                ->label('O — Objetivo')
                ->helperText('Hallazgos clínicos observados.')
                ->rows(2),

            Textarea::make('assessment')
                ->label('A — Evaluación')
                ->helperText('Diagnóstico/interpretación clínica.')
                ->rows(2),

            Textarea::make('plan')
                ->label('P — Plan')
                ->helperText('Siguientes pasos o tratamiento indicado.')
                ->rows(2),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->columns([
                TextColumn::make('visit_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(40),

                TextColumn::make('dentist.name')
                    ->label('Odontólogo'),

                TextColumn::make('diagnosis_cie10')
                    ->label('CIE-10')
                    ->badge(),
            ])
            ->defaultSort('visit_date', 'desc')
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
