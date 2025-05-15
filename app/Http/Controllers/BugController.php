<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bug;
use Illuminate\Support\Facades\Log;
use App\Models\QaChecklistItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use GuzzleHttp\Client;

class BugController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bugs = Bug::with('assignee','fixes','team')->get();
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
        try {
            // Validate the request data first
            $validated = $request->validate([
                'title' => 'required|string',
                'description' => 'required|string',
                'steps_to_reproduce' => 'nullable|string',
                'expected_behavior' => 'nullable|string',
                'actual_behavior' => 'nullable|string',
                'device' => 'nullable|string',
                'browser' => 'nullable|string',
                'os' => 'nullable|string',
                'status' => ['nullable', 'string', function ($attribute, $value, $fail) {
                    $validStatuses = ['new', 'open', 'in_progress', 'resolved', 'closed'];
                    if (!in_array(strtolower($value), $validStatuses)) {
                        $fail('The selected status is invalid.');
                    }
                }],
                'priority' => ['nullable', 'string', function ($attribute, $value, $fail) {
                    $validPriorities = ['low', 'medium', 'high', 'critical'];
                    if (!in_array(strtolower($value), $validPriorities)) {
                        $fail('The selected priority is invalid.');
                    }
                }],
                'assignee_id' => 'nullable|exists:users,id',
                'url' => 'nullable|string',
                'team_id' => 'nullable|exists:teams,id',
                'reported_by' => 'nullable|exists:users,id',
                'qa_list_item_id' => 'nullable|exists:qa_checklist_items,id',
                'screenshot' => ['nullable', function ($attribute, $value, $fail) use ($request) {
                    if (is_string($value) && Str::startsWith($value, 'data:image')) {
                        // Validate base64 image
                        $imageData = explode(',', $value);
                        if (count($imageData) !== 2) {
                            $fail('Invalid image data format.');
                            return;
                        }
                        $decodedImage = base64_decode($imageData[1]);
                        if ($decodedImage === false) {
                            $fail('Invalid base64 image data.');
                            return;
                        }
                        // Check file size (10MB limit)
                        if (strlen($decodedImage) > 10 * 1024 * 1024) {
                            $fail('The screenshot must not be greater than 10MB.');
                            return;
                        }
                        // Check if it's a valid image
                        if (!getimagesizefromstring($decodedImage)) {
                            $fail('The screenshot must be a valid image.');
                            return;
                        }
                    } elseif ($request->hasFile('screenshot')) {
                        // Validate file upload
                        $file = $request->file('screenshot');
                        if (!$file->isValid()) {
                            $fail('Invalid file upload.');
                            return;
                        }
                        if (!$file->getMimeType() || !Str::startsWith($file->getMimeType(), 'image/')) {
                            $fail('The screenshot must be an image.');
                            return;
                        }
                        if ($file->getSize() > 10 * 1024 * 1024) {
                            $fail('The screenshot must not be greater than 10MB.');
                            return;
                        }
                    }
                }],
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);

            // Handle screenshot upload if present
            $screenshotUrl = null;
            if ($request->has('screenshot')) {
                try {
                    if (is_string($request->screenshot) && Str::startsWith($request->screenshot, 'data:image')) {
                        // Handle base64 image
                        $imageData = explode(',', $request->screenshot);
                        $decodedImage = base64_decode($imageData[1]);
                        $extension = explode('/', explode(';', $imageData[0])[0])[1];
                        $filename = 'screenshot_' . time() . '.' . $extension;

                        // Save to storage
                        $path = storage_path('app/public/screenshots/' . $filename);
                        file_put_contents($path, $decodedImage);

                        // Get public URL
                        $screenshotUrl = asset('storage/screenshots/' . $filename);
                    } elseif ($request->hasFile('screenshot')) {
                        // Handle file upload
                        $screenshotUrl = $this->uploadScreenshotToFirebase($request->file('screenshot'));
                    }

                    Log::info('Screenshot uploaded successfully', ['url' => $screenshotUrl]);
                } catch (\Exception $e) {
                    Log::error('Failed to upload screenshot', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'message' => 'Failed to upload screenshot',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            // Map the data to our model's structure
            $mappedData = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'steps_to_reproduce' => $request->input('steps_to_reproduce'),
                'expected_behavior' => $request->input('expected_behavior'),
                'actual_behavior' => $request->input('actual_behavior'),
                'status' => strtolower($request->input('status', 'new')),
                'priority' => strtolower($request->input('priority', 'medium')),
                'assignee_id' => $request->input('assignee_id'),
                'url' => $request->input('url'),
                'screenshot' => $screenshotUrl,
                'device' => $request->input('device'),
                'browser' => $request->input('browser'),
                'os' => $request->input('os'),
                'team_id' => $request->input('team_id'),
                'reported_by' => $request->input('reported_by', auth()->id()),
            ];

            // If relatedItem is provided, find the QaChecklistItem and link it
            if ($request->has('relatedItem')) {
                $qaListItem = QaChecklistItem::where('identifier', $request->input('relatedItem'))->first();
                if ($qaListItem) {
                    $mappedData['qa_list_item_id'] = $qaListItem->id;
                    Log::info('QA checklist item linked', [
                        'relatedItem' => $request->input('relatedItem'),
                        'qa_list_item_id' => $qaListItem->id
                    ]);
                } else {
                    Log::warning('QA checklist item not found', [
                        'relatedItem' => $request->input('relatedItem')
                    ]);
                }
            }

            // Log the mapped data for debugging
            Log::info('Mapped data', [
                'mapped_data' => $mappedData
            ]);

            // Create the bug
            $bug = Bug::create($mappedData);

            // Log the created bug
            Log::info('Bug created successfully', [
                'bug' => $bug->toArray()
            ]);

            return response()->json($bug->load(['assignee', 'team']), 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create bug', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'message' => 'Failed to create bug',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload screenshot to Firebase Storage
     */
    private function uploadScreenshotToFirebase($file)
    {
        // Generate a unique filename
        $filename = 'screenshots/' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Store the file in the public disk
        $path = $file->storeAs('public/screenshots', $filename);

        // Return the public URL for the file
        return asset('storage/' . $path);
    }

    /**
     * Get Firebase Custom Token
     */
    private function getFirebaseCustomToken()
    {
        $config = config('firebase');

        // Create a custom token using Firebase Admin SDK
        $response = Http::post('https://identitytoolkit.googleapis.com/v1/projects/' . $config['projectId'] . '/accounts:signInWithCustomToken', [
            'key' => $config['apiKey'],
            'token' => $this->generateFirebaseCustomToken()
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to get Firebase Custom Token: ' . $response->body());
        }

        return $response->json('idToken');
    }

    /**
     * Generate Firebase Custom Token
     */
    private function generateFirebaseCustomToken()
    {
        $config = config('firebase');

        // Create a JWT token for Firebase
        $now = time();
        $payload = [
            'iss' => $config['projectId'] . '@appspot.gserviceaccount.com',
            'sub' => $config['projectId'] . '@appspot.gserviceaccount.com',
            'aud' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
            'iat' => $now,
            'exp' => $now + 3600,
            'uid' => 'server'
        ];

        // You'll need to implement JWT signing here using your Firebase service account private key
        // For now, we'll use a simple token for testing
        return base64_encode(json_encode($payload));
    }

    /**
     * Display the specified resource.
     */
    public function show(Bug $bug)
    {
        return response()->json($bug->load('assignee'));
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
            'screenshot' => 'nullable|string',
            'team_id' => 'nullable|exists:teams,id'
        ]);

        $bug->update($validated);
        return response()->json($bug->load(['assignee', 'team']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bug $bug)
    {
        $bug->delete();
        return response()->json(null, 204);
    }

    /**
     * Fix a bug: update status and save findings/solutions.
     */
    public function fixBug(Request $request, $bugId)
    {
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

        $bug = Bug::findOrFail($bugId);
        $bug->status = $data['status'];
        $bug->save();
        Log::info('Bug status updated', ['bug_id' => $bugId, 'status' => $bug->status]);

        $fix = $bug->fixes()->create([
            'findings' => $data['findings'],
            'solutions' => $data['solution'], // Note: changed from solutions to solution to match request
        ]);
        Log::info('Bug fix saved', ['bug_fix_id' => $fix->id, 'bug_id' => $bugId]);

        return response()->json([
            'bug' => $bug,
            'fix' => $fix
        ]);
    }
}
