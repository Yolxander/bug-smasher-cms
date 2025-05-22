<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QaChecklist;
use App\Models\QaChecklistItem;
use App\Models\QaChecklistResponse;
use App\Models\QaChecklistAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QaChecklistController extends Controller
{
    public function index()
    {
        try {

            if (!auth()->check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $checklists = QaChecklist::with(['creator', 'items.bugs', 'assignments.user', 'assignedUsers'])
                ->where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json($checklists);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred',
                'message' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch QA checklists',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        Log::debug('Storing new QA checklist', $request->all());

        // Convert pending status to accepted in the request data
        $requestData = $request->all();
        if (isset($requestData['data']['attributes']['assignments'])) {
            foreach ($requestData['data']['attributes']['assignments'] as &$assignment) {
                if ($assignment['status'] === 'pending') {
                    $assignment['status'] = 'accepted';
                }
            }
        }
        $request->merge($requestData);

        $validator = Validator::make($request->all(), [
            'data.type' => 'required|in:qa_checklists',
            'data.attributes.title' => 'required|string|max:255',
            'data.attributes.description' => 'nullable|string',
            'data.attributes.status' => 'required|in:draft,active,archived',
            'data.attributes.priority' => 'required|in:low,medium,high',
            'data.attributes.tags' => 'nullable|array',
            'data.attributes.category' => 'nullable|string',
            'data.attributes.items' => 'required|array',
            'data.attributes.items.*.text' => 'required|string',
            'data.attributes.items.*.type' => 'required|in:text',
            'data.attributes.items.*.is_required' => 'required|boolean',
            'data.attributes.items.*.order_number' => 'required|integer',
            'data.attributes.items.*.status' => 'required|in:passed,failed,pending',
            'data.attributes.assignments' => 'required|array',
            'data.attributes.assignments.*.user_id' => 'required|exists:users,id',
            'data.attributes.assignments.*.due_date' => 'required|date',
            'data.attributes.assignments.*.status' => 'required|in:accepted,rejected',
        ]);

        if ($validator->fails()) {
            Log::warning('Checklist creation failed validation', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $data = $request->input('data.attributes');

                // Find or create category if provided
                $categoryId = null;
                if (!empty($data['category'])) {
                    $category = \App\Models\QaChecklistCategory::firstOrCreate(
                        ['name' => $data['category']],
                        [
                            'slug' => \Illuminate\Support\Str::slug($data['category']),
                            'is_active' => true,
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id(),
                        ]
                    );
                    $categoryId = $category->id;
                }

                // Create the checklist
                $checklist = QaChecklist::create([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'priority' => $data['priority'],
                    'tags' => $data['tags'] ?? [],
                    'category_id' => $categoryId,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                Log::info('Checklist created', ['id' => $checklist->id]);

                // Create checklist items
                foreach ($data['items'] as $item) {
                    $checklist->items()->create([
                        'item_text' => $item['text'],
                        'item_type' => $item['type'],
                        'is_required' => $item['is_required'],
                        'order_number' => $item['order_number'],
                        'status' => $item['status'],
                    ]);
                    Log::info('Checklist item created', $item);
                }

                // Delete any existing assignments for this checklist
                $checklist->assignments()->delete();

                // Create assignments
                $assignments = collect($data['assignments'])->map(function ($assignment) use ($checklist) {
                    return [
                        'user_id' => $assignment['user_id'],
                        'status' => $assignment['status'],
                        'due_date' => $assignment['due_date'],
                        'notes' => $assignment['notes'] ?? null,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                        'qa_checklist_id' => $checklist->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();

                // Bulk insert assignments
                DB::table('qa_checklist_assignments')->insert($assignments);

                foreach ($data['assignments'] as $assignment) {
                    Log::info('Assignment created', $assignment);
                }

                return response()->json($checklist->load(['items', 'assignments.user', 'assignedUsers']), 201);
            });
        } catch (\Exception $e) {
            Log::error('Failed to create checklist', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to create checklist',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(QaChecklist $qaChecklist)
    {
        Log::debug('Showing checklist', ['id' => $qaChecklist->id]);
        return response()->json($qaChecklist->load([
            'items',
            'items.bugs',
            'responses',
            'creator',
            'assignments.user',
            'assignedUsers'
        ]));
    }

    public function update(Request $request, QaChecklist $qaChecklist)
    {
        Log::debug('Updating checklist', [
            'checklist_id' => $qaChecklist->id,
            'data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            // ...validation rules
        ]);

        if ($validator->fails()) {
            Log::warning('Checklist update failed validation', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $qaChecklist->update([
            // ...updated fields
        ]);

        // Update assignments if provided
        if ($request->has('assigned_users')) {
            // Remove existing assignments
            $qaChecklist->assignments()->delete();

            // Create new assignments
            foreach ($request->assigned_users as $userId) {
                $qaChecklist->assignments()->create([
                    'user_id' => $userId,
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                    'status' => 'accepted'
                ]);
            }
        }

        Log::info('Checklist updated', ['id' => $qaChecklist->id]);
        return response()->json($qaChecklist->fresh(['items', 'assignments.user', 'assignedUsers']));
    }

    public function destroy(QaChecklist $qaChecklist)
    {
        Log::info('Soft deleting checklist', ['id' => $qaChecklist->id]);
        $qaChecklist->update(['is_deleted' => true]);
        return response()->json(null, 204);
    }

    public function addItem(Request $request, QaChecklist $qaChecklist)
    {
        Log::debug('Adding item to checklist', [
            'checklist_id' => $qaChecklist->id,
            'item_data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            // ...validation rules
        ]);

        if ($validator->fails()) {
            Log::warning('Add item failed validation', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = $qaChecklist->items()->create($request->all());
        Log::info('Item added', ['item_id' => $item->id]);
        return response()->json($item, 201);
    }

    public function submitResponse(Request $request, QaChecklist $qaChecklist)
    {
        Log::debug('Submitting response for checklist', [
            'checklist_id' => $qaChecklist->id,
            'payload' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            // ...validation rules
        ]);

        if ($validator->fails()) {
            Log::warning('Response submission failed validation', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = $qaChecklist->responses()->create([
            // ...response fields
        ]);

        Log::info('Response submitted', ['response_id' => $response->id]);
        return response()->json($response, 201);
    }

    public function getResponses(QaChecklist $qaChecklist)
    {
        Log::debug('Fetching responses for checklist', ['checklist_id' => $qaChecklist->id]);

        $responses = $qaChecklist->responses()
            ->with(['item', 'responder'])
            ->orderBy('responded_at', 'desc')
            ->get();

        Log::debug('Responses fetched', ['count' => $responses->count()]);
        return response()->json($responses);
    }

    public function getActiveItems(QaChecklist $qaChecklist)
    {
        Log::debug('Fetching active items for checklist', ['checklist_id' => $qaChecklist->id]);
        return response()->json($qaChecklist->getActiveItems());
    }

    public function getCompletedItems(QaChecklist $qaChecklist)
    {
        Log::debug('Fetching completed items for checklist', ['checklist_id' => $qaChecklist->id]);
        return response()->json($qaChecklist->getCompletedItems());
    }

    ///UPDATE ITEM FUNCTION
    public function updateItem(Request $request, QaChecklist $qaChecklist, QaChecklistItem $item)
    {
        Log::debug('Updating checklist item', [
            'checklist_id' => $qaChecklist->id,
            'item_id' => $item->id,
            'data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'order_number' => 'required|integer',
            'status' => 'required|in:passed,failed,pending',
            'answer' => 'nullable|string',
            'failure_reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            Log::warning('Item update failed validation', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->update([
            'order_number' => $request->order_number,
            'status' => $request->status,
            'answer' => $request->answer,
            'failure_reason' => $request->failure_reason
        ]);

        Log::info('Item updated', ['item_id' => $item->id]);
        return response()->json($item);
    }

    public function deleteItem(QaChecklist $qaChecklist, QaChecklistItem $item)
    {
        Log::info('Deleting checklist item', ['item_id' => $item->id, 'checklist_id' => $qaChecklist->id]);
        $item->delete();
        return response()->json(null, 204);
    }

    public function getAssignedUsers(QaChecklist $qaChecklist)
    {
        Log::debug('Fetching assigned users for checklist', ['checklist_id' => $qaChecklist->id]);
        return response()->json($qaChecklist->assignedUsers);
    }

    public function getAssignments(QaChecklist $qaChecklist)
    {
        Log::debug('Fetching assignments for checklist', ['checklist_id' => $qaChecklist->id]);
        return response()->json($qaChecklist->assignments()->with('user')->get());
    }

    public function getStats()
    {
        try {
            // Get current period stats
            $currentPeriod = now()->subDays(30);

            // Active Projects (QA Checklists)
            $activeProjectsCount = QaChecklist::where('is_deleted', false)
                ->where('status', 'active')
                ->count();

            $previousActiveProjectsCount = QaChecklist::where('is_deleted', false)
                ->where('status', 'active')
                ->where('created_at', '<', $currentPeriod)
                ->count();

            $activeProjectsChange = $previousActiveProjectsCount > 0
                ? (($activeProjectsCount - $previousActiveProjectsCount) / $previousActiveProjectsCount) * 100
                : 0;

            // Completed Items
            $completedItemsCount = QaChecklistItem::whereHas('responses', function($query) {
                $query->where('status', 'completed');
            })->count();

            $previousCompletedItemsCount = QaChecklistItem::whereHas('responses', function($query) use ($currentPeriod) {
                $query->where('status', 'completed')
                    ->where('responded_at', '<', $currentPeriod);
            })->count();

            $completedItemsChange = $previousCompletedItemsCount > 0
                ? (($completedItemsCount - $previousCompletedItemsCount) / $previousCompletedItemsCount) * 100
                : 0;

            // Active Reviewers
            $activeReviewersCount = QaChecklistAssignment::where('status', 'accepted')
                ->where('assigned_at', '>=', $currentPeriod)
                ->distinct('user_id')
                ->count('user_id');

            $previousActiveReviewersCount = QaChecklistAssignment::where('status', 'accepted')
                ->whereBetween('assigned_at', [$currentPeriod->copy()->subDays(30), $currentPeriod])
                ->distinct('user_id')
                ->count('user_id');

            $activeReviewersChange = $previousActiveReviewersCount > 0
                ? (($activeReviewersCount - $previousActiveReviewersCount) / $previousActiveReviewersCount) * 100
                : 0;

            return response()->json([
                'activeProjects' => [
                    'count' => $activeProjectsCount,
                    'change' => round($activeProjectsChange, 2),
                    'period' => '30 days'
                ],
                'completedItems' => [
                    'count' => $completedItemsCount,
                    'change' => round($completedItemsChange, 2),
                    'period' => '30 days'
                ],
                'activeReviewers' => [
                    'count' => $activeReviewersCount,
                    'change' => round($activeReviewersChange, 2),
                    'period' => '30 days'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching QA stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to fetch QA statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
