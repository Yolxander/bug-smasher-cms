<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bug extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'priority',
        'type',
        'assigned_to',
        'reported_by',
        'team_id',
        'due_date',
        'reproduction_steps',
        'expected_behavior',
        'actual_behavior',
        'additional_notes'
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(Profile::class, 'assigned_to');
    }

    public function reportedBy()
    {
        return $this->belongsTo(Profile::class, 'reported_by');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
