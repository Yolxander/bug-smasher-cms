<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\AsanaService;

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

        // Sync completion status with Asana
        static::updated(function ($item) {
            if ($item->isDirty('status')) {
                try {
                    // Find the Asana ticket associated with this checklist
                    $asanaTicket = AsanaTicket::whereHas('qaChecklistItem', function ($query) use ($item) {
                        $query->where('checklist_id', $item->checklist_id);
                    })->first();

                    if ($asanaTicket && $asanaTicket->asana_task_id) {
                        $asanaService = new AsanaService();

                        // Get all subtasks for the main task
                        $response = Http::withHeaders([
                            'Authorization' => 'Bearer ' . config('services.asana.pat'),
                            'Content-Type' => 'application/json',
                        ])->get("https://app.asana.com/api/1.0/tasks/{$asanaTicket->asana_task_id}/subtasks");

                        if ($response->successful()) {
                            $subtasks = $response->json()['data'];

                            // Find the matching subtask by name
                            foreach ($subtasks as $subtask) {
                                if ($subtask['name'] === $item->item_text) {
                                    // Update the subtask's completion status
                                    $asanaService->updateSubtaskStatus(
                                        $subtask['gid'],
                                        $item->status === 'passed'
                                    );
                                    break;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to sync QA checklist item status with Asana', [
                        'item_id' => $item->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
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

    public function bugs()
    {
        return $this->hasMany(Bug::class, 'qa_list_item_id');
    }
}
