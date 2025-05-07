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
        'order_number'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order_number' => 'integer'
    ];

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
