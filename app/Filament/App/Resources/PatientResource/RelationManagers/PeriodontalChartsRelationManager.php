<?php

namespace App\Filament\App\Resources\PatientResource\RelationManagers;

use App\Models\ToothDefinition;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PeriodontalChartsRelationManager extends RelationManager
{
    protected static string $relationship = 'periodontalCharts';

    protected static ?string $title = 'Periodontogramas';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DateTimePicker::make('recorded_at')
                ->label('Fecha')
                ->required()
                ->default(now())
                ->native(false),

            Textarea::make('notes')
                ->label('Notas generales')
                ->columnSpanFull(),

            Repeater::make('measurements')
                ->relationship()
                ->label('Mediciones')
                ->schema([
                    Select::make('tooth_fdi_code')
                        ->label('Diente')
                        ->options(fn () => ToothDefinition::query()->ordered()->pluck('name', 'fdi_code'))
                        ->searchable()
                        ->required(),

                    Select::make('site')
                        ->label('Sitio')
                        ->options([
                            'mesial_buccal'  => 'Mesio-vestibular',
                            'mid_buccal'     => 'Medio-vestibular',
                            'distal_buccal'  => 'Disto-vestibular',
                            'mesial_lingual' => 'Mesio-lingual/palatino',
                            'mid_lingual'    => 'Medio-lingual/palatino',
                            'distal_lingual' => 'Disto-lingual/palatino',
                        ])
                        ->required(),

                    TextInput::make('probing_depth_mm')
                        ->label('Profundidad sondaje (mm)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(20),

                    TextInput::make('recession_mm')
                        ->label('Recesión (mm)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(20),

                    TextInput::make('mobility_grade')
                        ->label('Movilidad (0-3)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(3),

                    TextInput::make('furcation_grade')
                        ->label('Furca (0-3)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(3),

                    Toggle::make('bleeding_on_probing')
                        ->label('Sangrado al sondaje'),

                    Toggle::make('plaque_present')
                        ->label('Placa presente'),
                ])
                ->columns(4)
                ->columnSpanFull()
                ->defaultItems(0)
                ->addActionLabel('Agregar medición')
                ->collapsible(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('recorded_at')
            ->columns([
                TextColumn::make('recorded_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('recordedBy.name')
                    ->label('Registrado por'),

                TextColumn::make('measurements_count')
                    ->label('Mediciones')
                    ->counts('measurements'),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['recorded_by'] = Auth::id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
