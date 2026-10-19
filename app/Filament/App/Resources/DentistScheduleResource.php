<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\DentistScheduleResource\Pages;
use App\Models\DentistSchedule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DentistScheduleResource extends Resource
{
    protected static ?string $model = DentistSchedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Agenda';

    protected static ?string $modelLabel = 'horario';

    protected static ?string $pluralModelLabel = 'horarios';

    protected static ?int $navigationSort = 3;

    public const DAYS = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Odontólogo')
                ->relationship('dentist', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('chair_id')
                ->label('Sillón')
                ->relationship('chair', 'name')
                ->searchable()
                ->preload(),

            Select::make('day_of_week')
                ->label('Día')
                ->options(self::DAYS)
                ->required(),

            TimePicker::make('start_time')
                ->label('Hora inicio')
                ->required()
                ->native(false)
                ->seconds(false),

            TimePicker::make('end_time')
                ->label('Hora fin')
                ->required()
                ->native(false)
                ->seconds(false)
                ->after('start_time'),
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

                TextColumn::make('day_of_week')
                    ->label('Día')
                    ->formatStateUsing(fn (int $state) => self::DAYS[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Inicio')
                    ->time('H:i'),

                TextColumn::make('end_time')
                    ->label('Fin')
                    ->time('H:i'),

                TextColumn::make('chair.name')
                    ->label('Sillón'),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Odontólogo')
                    ->relationship('dentist', 'name'),

                SelectFilter::make('day_of_week')
                    ->label('Día')
                    ->options(self::DAYS),
            ])
            ->defaultSort('day_of_week');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDentistSchedules::route('/'),
            'create' => Pages\CreateDentistSchedule::route('/create'),
            'edit'   => Pages\EditDentistSchedule::route('/{record}/edit'),
        ];
    }
}
