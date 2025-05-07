<?php

namespace App\Filament\Resources\QaChecklistTemplateResource\Pages;

use App\Filament\Resources\QaChecklistTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQaChecklistTemplates extends ListRecords
{
    protected static string $resource = QaChecklistTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
