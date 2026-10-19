<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Agenda';

    protected static ?string $modelLabel = 'cita';

    protected static ?string $pluralModelLabel = 'citas';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('patient_id')
                ->label('Paciente')
                ->relationship('patient', 'first_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                ->searchable(['first_name', 'last_name'])
                ->preload()
                ->required(),

            Select::make('dentist_id')
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

            Select::make('treatment_id')
                ->label('Tratamiento')
                ->relationship('treatment', 'name')
                ->searchable()
                ->preload(),

            DateTimePicker::make('scheduled_start')
                ->label('Inicio')
                ->required()
                ->native(false)
                ->seconds(false),

            DateTimePicker::make('scheduled_end')
                ->label('Fin')
                ->required()
                ->native(false)
                ->seconds(false)
                ->after('scheduled_start'),

            Select::make('status')
                ->label('Estado')
                ->options([
                    'scheduled'   => 'Programada',
                    'confirmed'   => 'Confirmada',
                    'waiting'     => 'En espera',
                    'in_progress' => 'En atención',
                    'completed'   => 'Completada',
                    'cancelled'   => 'Cancelada',
                    'no_show'     => 'No asistió',
                ])
                ->required()
                ->default('scheduled'),

            Textarea::make('reason')
                ->label('Motivo')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.full_name')
                    ->label('Paciente')
                    ->searchable(['patients.first_name', 'patients.last_name'])
                    ->sortable(),

                TextColumn::make('dentist.name')
                    ->label('Odontólogo')
                    ->sortable(),

                TextColumn::make('chair.name')
                    ->label('Sillón'),

                TextColumn::make('scheduled_start')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled'   => 'gray',
                        'confirmed'   => 'info',
                        'waiting'     => 'warning',
                        'in_progress' => 'warning',
                        'completed'   => 'success',
                        'cancelled', 'no_show' => 'danger',
                        default       => 'gray',
                    }),

                TextColumn::make('treatment.name')
                    ->label('Tratamiento')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'scheduled'   => 'Programada',
                        'confirmed'   => 'Confirmada',
                        'waiting'     => 'En espera',
                        'in_progress' => 'En atención',
                        'completed'   => 'Completada',
                        'cancelled'   => 'Cancelada',
                        'no_show'     => 'No asistió',
                    ]),

                SelectFilter::make('dentist_id')
                    ->label('Odontólogo')
                    ->relationship('dentist', 'name'),

                Filter::make('today')
                    ->label('Hoy')
                    ->query(fn (Builder $query) => $query->whereDate('scheduled_start', today()))
                    ->toggle(),

                Filter::make('upcoming')
                    ->label('Próximas')
                    ->query(fn (Builder $query) => $query->upcoming())
                    ->toggle(),
            ])
            ->defaultSort('scheduled_start');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit'   => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
