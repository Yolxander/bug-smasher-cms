<?php

namespace App\Filament\Resources\QaChecklistResource\Pages;

use App\Filament\Resources\QaChecklistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQaChecklists extends ListRecords
{
    protected static string $resource = QaChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
