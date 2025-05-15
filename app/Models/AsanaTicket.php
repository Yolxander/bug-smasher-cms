<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsanaTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'bug_id',
        'qa_checklist_item_id',
        'status',
        'notes',
        'ticket_type'
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
}
