<?php

namespace App\Filament\Resources\QaChecklistCategoryResource\Pages;

use App\Filament\Resources\QaChecklistCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQaChecklistCategory extends EditRecord
{
    protected static string $resource = QaChecklistCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
