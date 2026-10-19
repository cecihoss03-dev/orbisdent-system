<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ScheduleExceptionResource\Pages;
use App\Models\ScheduleException;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScheduleExceptionResource extends Resource
{
    protected static ?string $model = ScheduleException::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-date-range';

    protected static string|\UnitEnum|null $navigationGroup = 'Agenda';

    protected static ?string $modelLabel = 'excepción de horario';

    protected static ?string $pluralModelLabel = 'excepciones de horario';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Odontólogo')
                ->relationship('dentist', 'name')
                ->searchable()
                ->preload()
                ->required(),

            DatePicker::make('date')
                ->label('Fecha')
                ->required()
                ->native(false),

            Select::make('type')
                ->label('Tipo')
                ->options([
                    'vacation'     => 'Vacaciones',
                    'block'        => 'Bloqueo',
                    'extra_hours'  => 'Horas extra',
                ])
                ->required()
                ->live(),

            TimePicker::make('start_time')
                ->label('Hora inicio')
                ->native(false)
                ->seconds(false)
                ->visible(fn ($get) => $get('type') !== 'vacation'),

            TimePicker::make('end_time')
                ->label('Hora fin')
                ->native(false)
                ->seconds(false)
                ->after('start_time')
                ->visible(fn ($get) => $get('type') !== 'vacation'),

            TextInput::make('reason')
                ->label('Motivo')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dentist.name')
                    ->label('Odontólogo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'vacation'    => 'Vacaciones',
                        'block'       => 'Bloqueo',
                        'extra_hours' => 'Horas extra',
                        default       => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'vacation'    => 'info',
                        'block'       => 'danger',
                        'extra_hours' => 'success',
                        default       => 'gray',
                    }),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(40),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Odontólogo')
                    ->relationship('dentist', 'name'),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'vacation'    => 'Vacaciones',
                        'block'       => 'Bloqueo',
                        'extra_hours' => 'Horas extra',
                    ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListScheduleExceptions::route('/'),
            'create' => Pages\CreateScheduleException::route('/create'),
            'edit'   => Pages\EditScheduleException::route('/{record}/edit'),
        ];
    }
}
