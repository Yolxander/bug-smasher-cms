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
        'category_id',
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
        'version' => 'integer',
        'tags' => 'array'
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(QaChecklistItem::class, 'checklist_id')->orderBy('order_number');
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

    public function category()
    {
        return $this->belongsTo(QaChecklistCategory::class);
    }

    public function getActiveItems()
    {
        return $this->items()->where('is_required', true)->get();
    }

    public function getCompletedItems()
    {
        return $this->items()->whereHas('responses', function ($query) {
            $query->where('status', 'completed');
        })->get();
    }

    public function assignments()
    {
        return $this->hasMany(QaChecklistAssignment::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'qa_checklist_assignments')
            ->withPivot(['status', 'assigned_at', 'due_date', 'notes'])
            ->withTimestamps();
    }
}
