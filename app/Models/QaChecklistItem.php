<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QaChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_id',
        'item_text',
        'item_type',
        'is_required',
        'order_number',
        'identifier',
        'answer',
        'status',
        'failure_reason'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order_number' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Get the last identifier
            $lastItem = self::orderBy('id', 'desc')->first();

            if ($lastItem && preg_match('/#U(\d+)/', $lastItem->identifier, $matches)) {
                $nextNumber = (int)$matches[1] + 1;
            } else {
                $nextNumber = 1;
            }

            // Generate new identifier with leading zeros
            $model->identifier = '#U' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    // Relationships
    public function checklist()
    {
        return $this->belongsTo(QaChecklist::class, 'checklist_id');
    }

    public function responses()
    {
        return $this->hasMany(QaChecklistResponse::class, 'item_id');
    }
}
