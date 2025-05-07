<?php

namespace App\Filament\Resources\QaChecklistTemplateResource\Pages;

use App\Filament\Resources\QaChecklistTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQaChecklistTemplate extends EditRecord
{
    protected static string $resource = QaChecklistTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
