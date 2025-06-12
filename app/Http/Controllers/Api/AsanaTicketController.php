<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\AsanaTicket;
use App\Models\Bug;
use App\Models\QaChecklist;
use App\Services\AsanaService;

class AsanaTicketController extends Controller
{
    /**
     * Create a new Asana ticket and sync with Asana.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_type' => 'required|in:bug,qa_checklist',
            'bug_id' => 'required_if:ticket_type,bug|nullable|exists:bugs,id',
            'qa_checklist_id' => 'required_if:ticket_type,qa_checklist|nullable|exists:qa_checklists,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Generate ticket number in format ASANA-YYYY-XXXX
        $data['ticket_number'] = 'ASANA-' . date('Y') . '-' . str_pad(AsanaTicket::max('id') + 1, 4, '0', STR_PAD_LEFT);

        // Ensure we have either bug_id or qa_checklist_id based on ticket_type
        if ($data['ticket_type'] === 'bug') {
            $data['qa_checklist_id'] = null;
        } else {
            $data['bug_id'] = null;
        }

        try {
            // Create the AsanaTicket record
            $ticket = AsanaTicket::create($data);
            $asanaService = new AsanaService();
            $taskData = [];

            if ($ticket->ticket_type === 'bug') {
                $bug = Bug::find($ticket->bug_id);
                if (!$bug) {
                    return response()->json(['error' => 'Bug not found.'], 404);
                }
                $taskData = [
                    'title' => "[Bug] {$bug->title} - {$ticket->ticket_number}",
                    'notes' => "**Description:**\n{$bug->description}\n\n" .
                        "**Steps to Reproduce:**\n{$bug->steps_to_reproduce}\n\n" .
                        "**Expected Behavior:**\n{$bug->expected_behavior}\n\n" .
                        "**Actual Behavior:**\n{$bug->actual_behavior}\n\n" .
                        "**Additional Notes:**\n{$bug->additional_notes}"
                ];
            } elseif ($ticket->ticket_type === 'qa_checklist') {
                $checklist = QaChecklist::with('items')->find($ticket->qa_checklist_id);
                if (!$checklist) {
                    return response()->json(['error' => 'QA Checklist not found.'], 404);
                }
                $taskData = [
                    'title' => "[QA] {$checklist->title} - {$ticket->ticket_number}",
                    'notes' => "**Checklist Title:** {$checklist->title}\n\n" .
                        "**Description:**\n{$checklist->description}"
                ];
            }

            // Create the main task in Asana
            $asanaResponse = $asanaService->createTask($taskData);
            $ticket->update(['asana_task_id' => $asanaResponse['data']['gid']]);

            // If it's a QA checklist ticket, create subtasks for all items
            if ($ticket->ticket_type === 'qa_checklist') {
                $checklist = QaChecklist::with('items')->find($ticket->qa_checklist_id);
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
                'ticket_id' => $ticket->id,
                'asana_task_id' => $asanaResponse['data']['gid']
            ]);

            return response()->json([
                'message' => 'Ticket created and synced with Asana successfully.',
                'ticket' => $ticket->fresh()
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create Asana ticket', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return response()->json([
                'error' => 'The ticket was created in the system but failed to sync with Asana.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
