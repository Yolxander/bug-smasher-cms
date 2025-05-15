<?php

namespace App\Filament\Resources\AsanaTicketResource\Pages;

use App\Filament\Resources\AsanaTicketResource;
use App\Services\AsanaService;
use App\Models\AsanaTicket;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class CreateAsanaTicket extends CreateRecord
{
    protected static string $resource = AsanaTicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate ticket number in format ASANA-YYYY-XXXX
        $data['ticket_number'] = 'ASANA-' . date('Y') . '-' . str_pad(AsanaTicket::max('id') + 1, 4, '0', STR_PAD_LEFT);

        // Ensure we have either bug_id or qa_checklist_item_id based on ticket_type
        if ($data['ticket_type'] === 'bug') {
            $data['qa_checklist_item_id'] = null;
        } else {
            $data['bug_id'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            // Create Asana task
            $asanaService = new AsanaService();

            // Prepare task data based on ticket type
            $taskData = [
                'title' => $this->record->ticket_number,
                'notes' => $this->record->notes ?? '',
            ];

            if ($this->record->ticket_type === 'bug' && $this->record->bug) {
                $taskData['title'] = "[Bug] {$this->record->bug->title} - {$this->record->ticket_number}";
                $taskData['notes'] = "Bug Description: {$this->record->bug->description}\n\n" .
                                   "Steps to Reproduce: {$this->record->bug->steps_to_reproduce}\n\n" .
                                   "Expected Behavior: {$this->record->bug->expected_behavior}\n\n" .
                                   "Actual Behavior: {$this->record->bug->actual_behavior}\n\n" .
                                   "Additional Notes: {$this->record->notes}";
            } elseif ($this->record->ticket_type === 'qa_checklist' && $this->record->qaChecklistItem) {
                $taskData['title'] = "[QA] {$this->record->qaChecklistItem->item_text} - {$this->record->ticket_number}";
                $taskData['notes'] = "QA Checklist Item: {$this->record->qaChecklistItem->item_text}\n\n" .
                                   "Additional Notes: {$this->record->notes}";
            }

            // Create the task in Asana
            $asanaResponse = $asanaService->createTask($taskData);

            // Log the successful creation
            Log::info('Asana ticket created successfully', [
                'ticket_id' => $this->record->id,
                'ticket_number' => $this->record->ticket_number,
                'asana_response' => $asanaResponse
            ]);

            // Show success notification
            Notification::make()
                ->title('Ticket created successfully')
                ->body("Ticket {$this->record->ticket_number} has been created in Asana")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('Error creating Asana ticket', [
                'error' => $e->getMessage(),
                'ticket_id' => $this->record->id
            ]);

            // Show error notification
            Notification::make()
                ->title('Error creating Asana ticket')
                ->body('There was an error creating the ticket in Asana. Please try again.')
                ->danger()
                ->send();
        }
    }
}
