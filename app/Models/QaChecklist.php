<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QaChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'created_by',
        'updated_by',
        'version',
        'category',
        'due_date',
        'priority',
        'tags',
        'attachments',
        'comments',
        'is_deleted'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'is_deleted' => 'boolean',
        'version' => 'integer'
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(QaChecklistItem::class, 'checklist_id');
    }

    public function responses()
    {
        return $this->hasMany(QaChecklistResponse::class, 'checklist_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
