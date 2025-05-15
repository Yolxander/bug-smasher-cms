<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\AsanaService;
use Illuminate\Support\Facades\Log;

class AsanaTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'bug_id',
        'qa_checklist_item_id',
        'status',
        'notes',
        'ticket_type',
        'asana_task_id',
    ];

    protected $casts = [
        'bug_id' => 'integer',
        'qa_checklist_item_id' => 'integer',
    ];

    /**
     * Get the bug that this ticket belongs to.
     */
    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class);
    }

    /**
     * Get the QA checklist item associated with this ticket.
     */
    public function qaChecklistItem(): BelongsTo
    {
        return $this->belongsTo(QaChecklistItem::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Handle deletion
        static::deleting(function ($ticket) {
            if ($ticket->asana_task_id) {
                try {
                    $asanaService = new AsanaService();
                    $asanaService->deleteTask($ticket->asana_task_id);
                } catch (\Exception $e) {
                    Log::error('Failed to delete Asana task during ticket deletion', [
                        'ticket_id' => $ticket->id,
                        'asana_task_id' => $ticket->asana_task_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        // Handle updates
        static::updated(function ($ticket) {
            if ($ticket->asana_task_id) {
                try {
                    $asanaService = new AsanaService();

                    // Prepare task data based on ticket type
                    $taskData = [
                        'title' => $ticket->ticket_number,
                        'notes' => $ticket->notes ?? '',
                    ];

                    if ($ticket->ticket_type === 'bug' && $ticket->bug) {
                        $taskData['title'] = "[Bug] {$ticket->bug->title} - {$ticket->ticket_number}";
                        $taskData['notes'] = "Bug Description: {$ticket->bug->description}\n\n" .
                                           "Steps to Reproduce: {$ticket->bug->steps_to_reproduce}\n\n" .
                                           "Expected Behavior: {$ticket->bug->expected_behavior}\n\n" .
                                           "Actual Behavior: {$ticket->bug->actual_behavior}\n\n" .
                                           "Additional Notes: {$ticket->notes}";
                    } elseif ($ticket->ticket_type === 'qa_checklist' && $ticket->qaChecklistItem) {
                        $taskData['title'] = "[QA] {$ticket->qaChecklistItem->item_text} - {$ticket->ticket_number}";
                        $taskData['notes'] = "QA Checklist Item: {$ticket->qaChecklistItem->item_text}\n\n" .
                                           "Additional Notes: {$ticket->notes}";
                    }

                    $asanaService->updateTask($ticket->asana_task_id, $taskData);
                } catch (\Exception $e) {
                    Log::error('Failed to update Asana task during ticket update', [
                        'ticket_id' => $ticket->id,
                        'asana_task_id' => $ticket->asana_task_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
    }
}
