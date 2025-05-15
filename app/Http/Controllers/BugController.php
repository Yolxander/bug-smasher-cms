<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bug;
use Illuminate\Support\Facades\Log;
use App\Models\QaChecklistItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\FirebaseService;

class BugController extends Controller
{
    use AuthorizesRequests;

    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Bug::class);
        $bugs = Bug::with('assignee', 'fixes', 'team')->get();
        return response()->json($bugs);
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
        $this->authorize('create', Bug::class);

        // Log the incoming request data
        Log::info('Bug report received', [
            'request_data' => $request->all()
        ]);

        // Extract data from JSON:API format
        $data = $request->input('data.attributes', []);
        $environment = $data['environment'] ?? [];

        // Handle file upload if present
        $screenshotUrl = null;
        if ($request->hasFile('screenshot')) {
            try {
                $screenshotUrl = $this->firebaseService->uploadFile(
                    $request->file('screenshot'),
                    'bugs/screenshots'
                );
            } catch (\Exception $e) {
                Log::error('Failed to upload screenshot', [
                    'error' => $e->getMessage()
                ]);
                return response()->json([
                    'message' => 'Failed to upload screenshot',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        // Map the data to our model's structure
        $mappedData = [
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'steps_to_reproduce' => $data['steps_to_reproduce'] ?? null,
            'expected_behavior' => $data['expected_behavior'] ?? null,
            'actual_behavior' => $data['actual_behavior'] ?? null,
            'status' => $data['status'] ?? null,
            'priority' => $data['priority'] ?? null,
            'assignee_id' => $data['assignee_id'] ?? null,
            'url' => $data['url'] ?? null,
            'screenshot' => $screenshotUrl,
            'device' => $environment['device'] ?? null,
            'browser' => $environment['browser'] ?? null,
            'os' => $environment['os'] ?? null,
            'team_id' => $data['team_id'] ?? null,
            'reported_by' => $data['reported_by'] ?? null,
        ];

        // If relatedItem is provided, find the QaChecklistItem and link it
        if (isset($data['relatedItem'])) {
            $qaListItem = QaChecklistItem::where('identifier', $data['relatedItem'])->first();
            if ($qaListItem) {
                $mappedData['qa_list_item_id'] = $qaListItem->id;
            }
        }

        // Log the mapped data for debugging
        Log::info('Mapped data', [
            'mapped_data' => $mappedData
        ]);

        $validated = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'steps_to_reproduce' => 'nullable|string',
            'expected_behavior' => 'nullable|string',
            'actual_behavior' => 'nullable|string',
            'device' => 'nullable|string',
            'browser' => 'nullable|string',
            'os' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'url' => 'nullable|string',
            'screenshot' => 'nullable|file|image|max:10240', // 10MB max
            'qa_list_item_id' => 'nullable|exists:qa_checklist_items,id',
            'team_id' => 'nullable|exists:teams,id',
            'reported_by' => 'nullable|exists:users,id'
        ]);

        // Log the validated data
        Log::info('Bug report validated', [
            'validated_data' => $validated
        ]);

        $bug = Bug::create($mappedData);

        // Log the created bug
        Log::info('Bug created', [
            'bug' => $bug->toArray()
        ]);

        return response()->json($bug->load(['assignee', 'team']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bug $bug)
    {
        $this->authorize('view', $bug);
        return response()->json($bug->load(['assignee', 'team']));
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
    public function update(Request $request, Bug $bug)
    {
        $this->authorize('update', $bug);

        // Handle file upload if present
        $screenshotUrl = $bug->screenshot;
        if ($request->hasFile('screenshot')) {
            try {
                // Delete old screenshot if exists
                if ($bug->screenshot) {
                    $this->firebaseService->deleteFile($bug->screenshot);
                }

                $screenshotUrl = $this->firebaseService->uploadFile(
                    $request->file('screenshot'),
                    'bugs/screenshots'
                );
            } catch (\Exception $e) {
                Log::error('Failed to upload screenshot', [
                    'error' => $e->getMessage()
                ]);
                return response()->json([
                    'message' => 'Failed to upload screenshot',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        $validated = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'steps_to_reproduce' => 'nullable|string',
            'expected_behavior' => 'nullable|string',
            'actual_behavior' => 'nullable|string',
            'device' => 'nullable|string',
            'browser' => 'nullable|string',
            'os' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'url' => 'nullable|string',
            'screenshot' => 'nullable|file|image|max:10240', // 10MB max
            'team_id' => 'nullable|exists:teams,id'
        ]);

        $validated['screenshot'] = $screenshotUrl;
        $bug->update($validated);
        return response()->json($bug->load(['assignee', 'team']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bug $bug)
    {
        $this->authorize('delete', $bug);

        // Delete screenshot from Firebase if exists
        if ($bug->screenshot) {
            try {
                $this->firebaseService->deleteFile($bug->screenshot);
            } catch (\Exception $e) {
                Log::error('Failed to delete screenshot', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        $bug->delete();
        return response()->json(null, 204);
    }

    /**
     * Fix a bug: update status and save findings/solutions.
     */
    public function fixBug(Request $request, $bugId)
    {
        $bug = Bug::findOrFail($bugId);
        $this->authorize('update', $bug);

        Log::info('Fix bug request received', [
            'bug_id' => $bugId,
            'request' => $request->all()
        ]);

        // Extract data from JSON:API format
        $data = $request->input('data.attributes', []);

        $validated = $request->validate([
            'data.attributes.status' => 'required|string',
            'data.attributes.findings' => 'required|string',
            'data.attributes.solution' => 'required|string',
        ]);

        $bug->status = $data['status'];
        $bug->save();
        Log::info('Bug status updated', ['bug_id' => $bugId, 'status' => $bug->status]);

        $fix = $bug->fixes()->create([
            'findings' => $data['findings'],
            'solutions' => $data['solution'],
        ]);
        Log::info('Bug fix saved', ['bug_fix_id' => $fix->id, 'bug_id' => $bugId]);

        return response()->json([
            'bug' => $bug->load(['assignee', 'team']),
            'fix' => $fix
        ]);
    }
}
