<?php

namespace App\Filament\Resources\AsanaTicketResource\Pages;

use App\Filament\Resources\AsanaTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAsanaTickets extends ListRecords
{
    protected static string $resource = AsanaTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
