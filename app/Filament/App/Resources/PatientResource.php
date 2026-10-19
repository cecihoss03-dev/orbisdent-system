<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\PatientResource\Pages;
use App\Filament\App\Resources\PatientResource\RelationManagers;
use App\Models\Patient;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class PatientResource extends Resource
{
    
    protected static ?string $model = Patient::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static string|UnitEnum|null $navigationGroup = 'Clínico';

    protected static ?string $modelLabel = 'paciente';

    protected static ?string $pluralModelLabel = 'pacientes';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'full_name';

    public const GENDERS = [
        'female' => 'Femenino',
        'male'   => 'Masculino',
        'other'  => 'Otro',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Paciente')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Identificación')
                        ->schema([
                            TextInput::make('first_name')
                                ->label('Nombres')
                                ->required()
                                ->maxLength(80),

                            TextInput::make('last_name')
                                ->label('Apellidos')
                                ->required()
                                ->maxLength(80),

                            Select::make('document_type')
                                ->label('Tipo de documento')
                                ->options([
                                    'CI'       => 'Cédula de identidad',
                                    'passport' => 'Pasaporte',
                                    'other'    => 'Otro',
                                ]),

                            TextInput::make('document_number')
                                ->label('N.º de documento')
                                ->maxLength(30),

                            DatePicker::make('birth_date')
                                ->label('Fecha de nacimiento')
                                ->native(false)
                                ->maxDate(now()),

                            Select::make('gender')
                                ->label('Sexo')
                                ->options(self::GENDERS),

                            Select::make('marital_status')
                                ->label('Estado civil')
                                ->options([
                                    'single'   => 'Soltero/a',
                                    'married'  => 'Casado/a',
                                    'divorced' => 'Divorciado/a',
                                    'widowed'  => 'Viudo/a',
                                ]),

                            TextInput::make('occupation')
                                ->label('Ocupación')
                                ->maxLength(100),
                        ])
                        ->columns(2),

                    Tab::make('Contacto')
                        ->schema([
                            TextInput::make('phone')
                                ->label('Teléfono')
                                ->tel()
                                ->maxLength(30),

                            TextInput::make('email')
                                ->label('Correo')
                                ->email()
                                ->maxLength(150),

                            TextInput::make('address')
                                ->label('Dirección')
                                ->columnSpanFull(),

                            TextInput::make('referred_by')
                                ->label('Referido por')
                                ->maxLength(120),

                            Textarea::make('notes')
                                ->label('Notas generales')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Tab::make('Historia médica')
                        ->schema([
                            Section::make()
                                ->relationship('medicalHistory')
                                ->schema([
                                    Select::make('blood_type')
                                        ->label('Tipo de sangre')
                                        ->options(array_combine(
                                            $types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
                                            $types,
                                        )),

                                    TagsInput::make('allergies')
                                        ->label('Alergias')
                                        ->placeholder('Escribe y presiona Enter')
                                        ->columnSpanFull(),

                                    TagsInput::make('systemic_diseases')
                                        ->label('Enfermedades sistémicas')
                                        ->placeholder('Ej. diabetes, hipertensión')
                                        ->columnSpanFull(),

                                    TagsInput::make('current_medications')
                                        ->label('Medicación actual')
                                        ->columnSpanFull(),

                                    Select::make('pregnancy_status')
                                        ->label('Embarazo')
                                        ->options([
                                            'not_applicable' => 'No aplica',
                                            'pregnant'       => 'Embarazada',
                                            'not_pregnant'   => 'No embarazada',
                                            'unknown'        => 'Desconocido',
                                        ]),

                                    Toggle::make('is_smoker')
                                        ->label('Fumador'),

                                    Select::make('alcohol_consumption')
                                        ->label('Consumo de alcohol')
                                        ->options([
                                            'none'       => 'Ninguno',
                                            'occasional' => 'Ocasional',
                                            'frequent'   => 'Frecuente',
                                        ]),

                                    Textarea::make('other_notes')
                                        ->label('Otras observaciones')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nombre')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name']),

                TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Teléfono'),

                TextColumn::make('birth_date')
                    ->label('Edad')
                    ->state(fn (Patient $record) => $record->birth_date?->age)
                    ->suffix(' años'),

                TextColumn::make('gender')
                    ->label('Sexo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::GENDERS[$state] ?? $state),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label('Sexo')
                    ->options(self::GENDERS),
            ])
            ->recordActions([
                Action::make('odontogram')
                    ->label('Odontograma')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url(fn (Patient $record) => static::getUrl('odontogram', ['record' => $record])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('first_name');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EmergencyContactsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\PeriodontalChartsRelationManager::class,
            RelationManagers\ClinicalEvolutionNotesRelationManager::class,
            RelationManagers\PrescriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'      => Pages\ListPatients::route('/'),
            'create'     => Pages\CreatePatient::route('/create'),
            'edit'       => Pages\EditPatient::route('/{record}/edit'),
            'odontogram' => Pages\ManageOdontogram::route('/{record}/odontogram'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'document_number'];
    }
}