<?php

namespace App\Filament\App\Resources\PatientResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documentos';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->label('Tipo')
                ->options([
                    'id_card'      => 'Carnet de identidad',
                    'consent_form' => 'Consentimiento firmado',
                    'insurance'    => 'Póliza de seguro',
                    'other'        => 'Otro',
                ])
                ->required(),

            FileUpload::make('file_url')
                ->label('Archivo')
                ->directory('patient-documents')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')->label('Tipo')->badge(),
                TextColumn::make('uploaded_at')->label('Subido')->dateTime(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['uploaded_by'] = Auth::id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
