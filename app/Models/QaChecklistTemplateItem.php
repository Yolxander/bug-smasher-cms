<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QaChecklistTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'qa_checklist_template_id',
        'question',
        'description',
        'is_required',
        'order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(QaChecklistTemplate::class, 'qa_checklist_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (!$item->order) {
                $maxOrder = static::where('qa_checklist_template_id', $item->qa_checklist_template_id)
                    ->max('order');
                $item->order = $maxOrder + 1;
            }
        });
    }
}
