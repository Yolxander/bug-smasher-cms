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
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use App\Services\AsanaService;
use App\Models\AsanaTicket;

class BugController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bugs = Bug::with(['assignee', 'team'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

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
        Log::info('Creating new bug', [
            'user_id' => auth()->id(),
            'request_data' => $request->except(['screenshot']),
            'has_screenshot' => $request->has('screenshot')
        ]);

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'priority' => ['required', 'string', function ($attribute, $value, $fail) {
                    $validPriorities = ['Low', 'Medium', 'High'];
                    if (!in_array($value, $validPriorities)) {
                        $fail('The selected priority is invalid.');
                    }
                }],
                'status' => ['required', 'string', function ($attribute, $value, $fail) {
                    $validStatuses = ['New', 'Open', 'In Progress', 'Resolved', 'Closed'];
                    if (!in_array($value, $validStatuses)) {
                        $fail('The selected status is invalid.');
                    }
                }],
                'assignee_id' => 'nullable|exists:users,id',
                'team_id' => 'nullable|exists:teams,id',
//                'project_id' => 'nullable|exists:projects,id',
                'url' => 'nullable|string',
                'device' => 'nullable|string',
                'browser' => 'nullable|string',
                'os' => 'nullable|string',
                'steps_to_reproduce' => 'nullable|string',
                'expected_behavior' => 'nullable|string',
                'actual_behavior' => 'nullable|string',
                'reported_by' => 'nullable|exists:users,id',
                'screenshot' => 'nullable|file|image|max:5120',
                'relatedItem' => 'nullable|string',
            ]);

            Log::info('Bug validation passed', [
                'validated_data' => array_keys($validated)
            ]);

            // Handle screenshot upload
            if ($request->hasFile('screenshot')) {
                try {
                    $file = $request->file('screenshot');
                    $filename = 'screenshot_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $s3Path = '2025/yolxi/' . $filename;

                    Log::info('Attempting to upload file to S3', [
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        's3_path' => $s3Path,
                        'bucket' => config('filesystems.disks.s3.bucket'),
                        'region' => config('filesystems.disks.s3.region'),
                        'aws_key' => config('filesystems.disks.s3.key') ? 'set' : 'not set',
                        'aws_secret' => config('filesystems.disks.s3.secret') ? 'set' : 'not set'
                    ]);

                    // Create S3 client
                    $s3Client = new S3Client([
                        'version' => 'latest',
                        'region'  => config('filesystems.disks.s3.region'),
                        'credentials' => [
                            'key'    => config('filesystems.disks.s3.key'),
                            'secret' => config('filesystems.disks.s3.secret'),
                        ],
                    ]);

                    // Upload file
                    $result = $s3Client->putObject([
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        'Key'    => $s3Path,
                        'Body'   => fopen($file->getRealPath(), 'rb'),
                        'ContentType' => $file->getMimeType(),
                    ]);

                    // Get the S3 URL
                    $screenshotUrl = $result['ObjectURL'];

                    Log::info('Screenshot uploaded successfully to S3', [
                        'url' => $screenshotUrl,
                        'filename' => $filename,
                        's3_path' => $s3Path,
                        'result' => $result
                    ]);
                } catch (AwsException $e) {
                    Log::error('AWS S3 Exception', [
                        'error' => $e->getMessage(),
                        'code' => $e->getAwsErrorCode(),
                        'request_id' => $e->getAwsRequestId(),
                        'type' => $e->getAwsErrorType(),
                        'trace' => $e->getTraceAsString(),
                        'file_info' => [
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'size' => $file->getSize()
                        ],
                        'aws_config' => [
                            'bucket' => config('filesystems.disks.s3.bucket'),
                            'region' => config('filesystems.disks.s3.region'),
                            'key_set' => config('filesystems.disks.s3.key') ? 'yes' : 'no',
                            'secret_set' => config('filesystems.disks.s3.secret') ? 'yes' : 'no'
                        ]
                    ]);
                    return response()->json([
                        'message' => 'Failed to upload screenshot to S3',
                        'error' => $e->getMessage()
                    ], 500);
                } catch (\Exception $e) {
                    Log::error('Failed to upload screenshot to S3', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'file_info' => [
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'size' => $file->getSize()
                        ],
                        'aws_config' => [
                            'bucket' => config('filesystems.disks.s3.bucket'),
                            'region' => config('filesystems.disks.s3.region'),
                            'key_set' => config('filesystems.disks.s3.key') ? 'yes' : 'no',
                            'secret_set' => config('filesystems.disks.s3.secret') ? 'yes' : 'no'
                        ]
                    ]);
                    return response()->json([
                        'message' => 'Failed to upload screenshot',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            // Find QA checklist item if relatedItem is provided
            $qaListItemId = null;
            if ($request->has('relatedItem')) {
                $qaListItem = QaChecklistItem::where('identifier', $request->relatedItem)->first();
                if ($qaListItem) {
                    $qaListItemId = $qaListItem->id;
                    Log::info('QA checklist item found', [
                        'identifier' => $request->relatedItem,
                        'qa_list_item_id' => $qaListItemId
                    ]);
                } else {
                    Log::warning('QA checklist item not found', [
                        'identifier' => $request->relatedItem
                    ]);
                }
            }

            // Map the data to our model's structure
            $mappedData = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'status' => $validated['status'],
                'assignee_id' => $validated['assignee_id'] ?? null,
                'team_id' => $validated['team_id'] ?? null,
//                'project_id' => $validated['project_id'] ?? null,
                'url' => $validated['url'] ?? null,
                'device' => $validated['device'] ?? null,
                'browser' => $validated['browser'] ?? null,
                'os' => $validated['os'] ?? null,
                'steps_to_reproduce' => $validated['steps_to_reproduce'] ?? null,
                'expected_behavior' => $validated['expected_behavior'] ?? null,
                'actual_behavior' => $validated['actual_behavior'] ?? null,
                'screenshot_url' => $screenshotUrl ?? null,
                'reported_by' => $validated['reported_by'] ?? auth()->id(),
                'created_by' => auth()->id(),
                'qa_list_item_id' => $qaListItemId,
            ];

            Log::info('Creating bug with mapped data', [
                'mapped_data' => array_keys($mappedData)
            ]);

            $bug = Bug::create($mappedData);

            Log::info('Bug created successfully', [
                'bug_id' => $bug->id,
                'title' => $bug->title,
                'status' => $bug->status,
                'qa_list_item_id' => $qaListItemId
            ]);

            // If the bug priority is High, create an Asana ticket
            if (strtolower($bug->priority) === 'high') {
                try {
                    $asanaService = new AsanaService();
                    $ticketNumber = 'BUG_SMASHER-' . date('Y') . '-' . str_pad(AsanaTicket::max('id') + 1, 4, '0', STR_PAD_LEFT);
                    $notes = "### Description\n{$bug->description}\n\n" .
                        "### Steps to Reproduce\n{$bug->steps_to_reproduce}\n\n" .
                        "### Expected Behavior\n{$bug->expected_behavior}\n\n" .
                        "### Actual Behavior\n{$bug->actual_behavior}\n\n" .
                        "### Additional Notes\n{$bug->additional_notes}\n\n";
                    if (!empty($bug->screenshot_url)) {
                        $notes .= "### Screenshot\n[View Screenshot]({$bug->screenshot_url})\n\n";
                    }
                    $taskData = [
                        'title' => "[Bug] {$bug->title} - {$ticketNumber}",
                        'notes' => $notes
                    ];
                    $asanaResponse = $asanaService->createTask($taskData);
                    $asanaTaskId = $asanaResponse['data']['gid'] ?? null;

                    // Create AsanaTicket record
                    $asanaTicket = AsanaTicket::create([
                        'ticket_number' => $ticketNumber,
                        'bug_id' => $bug->id,
                        'ticket_type' => 'bug',
                        'asana_task_id' => $asanaTaskId,
                        'status' => 'open',
                        'notes' => $bug->description,
                    ]);

                    Log::info('Asana ticket created for high priority bug', [
                        'bug_id' => $bug->id,
                        'asana_ticket_id' => $asanaTicket->id,
                        'asana_task_id' => $asanaTaskId
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create Asana ticket for high priority bug', [
                        'bug_id' => $bug->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json($bug->load(['assignee', 'team']), 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Bug validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['screenshot'])
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create bug', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['screenshot'])
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
        $bug->load('assignee');
        return response()->json($bug);
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
        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'severity' => 'sometimes|required|in:low,medium,high,critical',
                'status' => 'sometimes|required|in:open,in_progress,resolved,closed',
                'assignee_id' => 'nullable|exists:users,id',
                'team_id' => 'nullable|exists:teams,id',
                'screenshot' => 'nullable|string|max:255',
            ]);

            // Handle screenshot upload if provided
            if ($request->has('screenshot')) {
                try {
                    if (is_string($request->screenshot) && Str::startsWith($request->screenshot, 'data:image')) {
                        $imageData = explode(',', $request->screenshot);
                        $decodedImage = base64_decode($imageData[1]);
                        $extension = explode('/', explode(';', $imageData[0])[0])[1];
                        $filename = 'screenshot_' . time() . '.' . $extension;

                        $path = storage_path('app/public/screenshots/' . $filename);
                        file_put_contents($path, $decodedImage);

                        $screenshotUrl = asset('storage/screenshots/' . $filename);
                    } elseif ($request->hasFile('screenshot')) {
                        $screenshotUrl = $this->uploadScreenshotToFirebase($request->file('screenshot'));
                    }

                    $validated['screenshot_url'] = $screenshotUrl;
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Failed to upload screenshot',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            $bug->update($validated);

            return response()->json($bug->fresh(['assignee', 'team']));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update bug',
                'error' => $e->getMessage()
            ], 500);
        }
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

        $fix = $bug->fixes()->create([
            'findings' => $data['findings'],
            'solutions' => $data['solution'], // Note: changed from solutions to solution to match request
        ]);

        return response()->json([
            'bug' => $bug,
            'fix' => $fix
        ]);
    }
}
