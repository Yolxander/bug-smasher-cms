<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugFix extends Model
{
    use HasFactory;

    protected $fillable = [
        'bug_id',
        'findings',
        'solutions'
    ];

    /**
     * Get the bug that this fix belongs to.
     */
    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class);
    }
}
