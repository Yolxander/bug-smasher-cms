<?php

namespace App\Filament\Resources\QaChecklistCategoryResource\Pages;

use App\Filament\Resources\QaChecklistCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQaChecklistCategories extends ListRecords
{
    protected static string $resource = QaChecklistCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
