<?php

namespace App\Filament\Resources\QaChecklistAssignmentResource\Pages;

use App\Filament\Resources\QaChecklistAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQaChecklistAssignments extends ListRecords
{
    protected static string $resource = QaChecklistAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
