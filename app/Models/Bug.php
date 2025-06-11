<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bug extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'assignee_id',
        'type',
        'assigned_to',
        'reported_by',
        'team_id',
        'due_date',
        'steps_to_reproduce',
        'expected_behavior',
        'actual_behavior',
        'additional_notes',
        'device',
        'browser',
        'os',
        'url',
        'screenshot_url',
        'qa_list_item_id',
        'team_id'
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    /**
     * Get the user assigned to this bug.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function qaListItem()
    {
        return $this->belongsTo(QaChecklistItem::class, 'qa_list_item_id');
    }

    public function fixes()
    {
        return $this->hasMany(BugFix::class);
    }

    public function asanaTickets()
    {
        return $this->hasMany(AsanaTicket::class);
    }
}
