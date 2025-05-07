<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QaChecklistResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_id',
        'item_id',
        'response',
        'responded_by',
        'responded_at',
        'status'
    ];

    protected $casts = [
        'responded_at' => 'datetime'
    ];

    // Relationships
    public function checklist()
    {
        return $this->belongsTo(QaChecklist::class, 'checklist_id');
    }

    public function item()
    {
        return $this->belongsTo(QaChecklistItem::class, 'item_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
