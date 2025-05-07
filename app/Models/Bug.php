<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bug extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'steps_to_reproduce',
        'expected_behavior',
        'actual_behavior',
        'device',
        'browser',
        'os',
        'status',
        'priority',
        'assignee_id',
        'project',
        'url',
        'screenshot'
    ];

    protected $casts = [
        'project' => 'array',
    ];

    public function assignee()
    {
        return $this->belongsTo(Profile::class, 'assignee_id');
    }
}
