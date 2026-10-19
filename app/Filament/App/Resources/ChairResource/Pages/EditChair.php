<?php

namespace App\Filament\App\Resources\ChairResource\Pages;

use App\Filament\App\Resources\ChairResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChair extends EditRecord
{
    protected static string $resource = ChairResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
