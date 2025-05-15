<?php

namespace App\Filament\Resources\DocumentationPageResource\Pages;

use App\Filament\Resources\DocumentationPageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocumentationPages extends ListRecords
{
    protected static string $resource = DocumentationPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
