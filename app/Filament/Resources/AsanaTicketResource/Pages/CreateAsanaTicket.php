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
        $record = $this->record;
        $asanaService = new AsanaService();

        try {
            $taskData = [];

            if ($record->ticket_type === 'bug') {
                $bug = $record->bug;
                $taskData = [
                    'title' => "[Bug] {$bug->title} - {$record->ticket_number}",
                    'notes' => "**Description:**\n{$bug->description}\n\n" .
                        "**Steps to Reproduce:**\n{$bug->steps_to_reproduce}\n\n" .
                        "**Expected Behavior:**\n{$bug->expected_behavior}\n\n" .
                        "**Actual Behavior:**\n{$bug->actual_behavior}\n\n" .
                        "**Additional Notes:**\n{$bug->additional_notes}"
                ];
            } elseif ($record->ticket_type === 'qa_checklist') {
                $checklistItem = $record->qaChecklistItem;
                $checklist = $checklistItem->checklist;
                $taskData = [
                    'title' => "[QA] {$checklist->title} - {$record->ticket_number}",
                    'notes' => "**Checklist Title:** {$checklist->title}\n\n" .
                        "**Description:**\n{$checklist->description}"
                ];
            }

            // Create the main task in Asana
            $asanaResponse = $asanaService->createTask($taskData);
            $record->update(['asana_task_id' => $asanaResponse['data']['gid']]);

            // If it's a QA checklist ticket, create subtasks for all items
            if ($record->ticket_type === 'qa_checklist') {
                $checklist = $record->qaChecklistItem->checklist;
                foreach ($checklist->items as $item) {
                    try {
                        // Create subtask
                        $subtaskResponse = $asanaService->createSubtask(
                            $asanaResponse['data']['gid'],
                            $item->item_text
                        );

                        // If the item is already passed, mark the subtask as completed
                        if ($item->status === 'passed') {
                            $asanaService->updateSubtaskStatus(
                                $subtaskResponse['data']['gid'],
                                true
                            );
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to create Asana subtask', [
                            'error' => $e->getMessage(),
                            'item_id' => $item->id
                        ]);
                    }
                }
            }

            Log::info('Asana ticket created successfully', [
                'ticket_id' => $record->id,
                'asana_task_id' => $asanaResponse['data']['gid']
            ]);

            // Show success notification
            Notification::make()
                ->title('Ticket created successfully')
                ->body("Ticket {$this->record->ticket_number} has been created in Asana")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('Failed to create Asana ticket', [
                'error' => $e->getMessage(),
                'ticket_id' => $record->id
            ]);
            Notification::make()
                ->title('Error creating Asana ticket')
                ->body('The ticket was created in the system but failed to sync with Asana.')
                ->danger()
                ->send();
        }
    }
}
