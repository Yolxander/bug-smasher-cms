<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QaChecklist;
use App\Models\QaChecklistItem;
use App\Models\QaChecklistResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QaChecklistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $checklists = QaChecklist::with(['creator', 'items'])
            ->where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($checklists);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,active,archived',
            'category' => 'nullable|string|max:100',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|string|max:50',
            'tags' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*.item_text' => 'required|string',
            'items.*.item_type' => 'required|in:checkbox,radio,text',
            'items.*.is_required' => 'required|boolean',
            'items.*.order_number' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $checklist = QaChecklist::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'category' => $request->category,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'tags' => $request->tags
        ]);

        foreach ($request->items as $item) {
            $checklist->items()->create($item);
        }

        return response()->json($checklist->load('items'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(QaChecklist $qaChecklist)
    {
        return response()->json($qaChecklist->load(['items', 'responses', 'creator']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QaChecklist $qaChecklist)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:draft,active,archived',
            'category' => 'nullable|string|max:100',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|string|max:50',
            'tags' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $qaChecklist->update([
            'title' => $request->title ?? $qaChecklist->title,
            'description' => $request->description,
            'status' => $request->status ?? $qaChecklist->status,
            'updated_by' => Auth::id(),
            'category' => $request->category,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'tags' => $request->tags
        ]);

        return response()->json($qaChecklist->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QaChecklist $qaChecklist)
    {
        $qaChecklist->update(['is_deleted' => true]);
        return response()->json(null, 204);
    }

    public function addItem(Request $request, QaChecklist $qaChecklist)
    {
        $validator = Validator::make($request->all(), [
            'item_text' => 'required|string',
            'item_type' => 'required|in:checkbox,radio,text',
            'is_required' => 'required|boolean',
            'order_number' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = $qaChecklist->items()->create($request->all());
        return response()->json($item, 201);
    }

    public function submitResponse(Request $request, QaChecklist $qaChecklist)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:qa_checklist_items,id',
            'response' => 'required|string',
            'status' => 'required|in:pending,completed,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = $qaChecklist->responses()->create([
            'item_id' => $request->item_id,
            'response' => $request->response,
            'responded_by' => Auth::id(),
            'responded_at' => now(),
            'status' => $request->status
        ]);

        return response()->json($response, 201);
    }

    public function getResponses(QaChecklist $qaChecklist)
    {
        $responses = $qaChecklist->responses()
            ->with(['item', 'responder'])
            ->orderBy('responded_at', 'desc')
            ->get();

        return response()->json($responses);
    }
}
