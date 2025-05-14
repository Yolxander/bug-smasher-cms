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

        $validator = Validator::make($request->all(), [
            // ...validation rules
        ]);

        if ($validator->fails()) {
            Log::warning('Checklist creation failed validation', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $checklist = QaChecklist::create([
            // ...fields
        ]);

        Log::info('Checklist created', ['id' => $checklist->id]);

        foreach ($request->items as $item) {
            $checklist->items()->create($item);
            Log::info('Checklist item created', $item);
        }

        // Create assignment if users are provided
        if ($request->has('assigned_users')) {
            foreach ($request->assigned_users as $userId) {
                $checklist->assignments()->create([
                    'user_id' => $userId,
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                    'status' => 'accepted'
                ]);
            }
        }

        return response()->json($checklist->load(['items', 'assignments.user', 'assignedUsers']), 201);
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
}
