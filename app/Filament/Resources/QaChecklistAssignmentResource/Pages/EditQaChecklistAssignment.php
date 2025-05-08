<?php

namespace App\Filament\Resources\QaChecklistAssignmentResource\Pages;

use App\Filament\Resources\QaChecklistAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQaChecklistAssignment extends EditRecord
{
    protected static string $resource = QaChecklistAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
