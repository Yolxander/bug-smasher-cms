<?php

namespace App\Filament\Resources\QaChecklistResource\Pages;

use App\Filament\Resources\QaChecklistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQaChecklist extends EditRecord
{
    protected static string $resource = QaChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
