<?php

namespace App\Filament\Resources\AsanaTicketResource\Pages;

use App\Filament\Resources\AsanaTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsanaTicket extends EditRecord
{
    protected static string $resource = AsanaTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
