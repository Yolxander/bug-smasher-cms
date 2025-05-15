<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsanaService
{
    protected string $baseUrl = 'https://app.asana.com/api/1.0';
    protected string $accessToken;
    protected string $workspaceId;
    protected string $projectId;

    public function __construct()
    {
        $this->accessToken = config('services.asana.pat');
        $this->workspaceId = config('services.asana.workspace_id');
        $this->projectId = config('services.asana.project_id');

        if (empty($this->accessToken)) {
            throw new \Exception('Asana Personal Access Token is not configured. Please set ASANA_PAT in your .env file.');
        }
    }

    /**
     * Create a new task in Asana
     */
    public function createTask(array $data): array
    {
        try {
            Log::info('Attempting to create Asana task', [
                'data' => $data
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/tasks", [
                'data' => [
                    'name' => $data['title'] ?? 'New Task',
                    'notes' => $data['notes'] ?? '',
                    'workspace' => $this->workspaceId,
                    'projects' => [$this->projectId],
                    'assignee' => null,
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Asana task created successfully', [
                    'response' => $responseData,
                    'project' => 'Bug Smasher Tickets'
                ]);
                return $responseData;
            }

            $errorResponse = $response->json();
            $errorMessage = $errorResponse['errors'][0]['message'] ?? 'Unknown error';

            Log::error('Failed to create Asana task', [
                'status' => $response->status(),
                'response' => $errorResponse,
                'project' => 'Bug Smasher Tickets',
                'request_data' => $data
            ]);

            throw new \Exception("Failed to create Asana task: {$errorMessage}");

        } catch (\Exception $e) {
            Log::error('Error creating Asana task', [
                'error' => $e->getMessage(),
                'project' => 'Bug Smasher Tickets',
                'request_data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Get project details
     */
    public function getProjectDetails(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get("{$this->baseUrl}/projects/{$this->projectId}");

            if ($response->successful()) {
                return $response->json()['data'];
            }

            throw new \Exception('Failed to get project details: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Error getting Asana project details', [
                'error' => $e->getMessage(),
                'project' => 'Bug Smasher Tickets'
            ]);
            throw $e;
        }
    }

    /**
     * Test the connection to Asana
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get("{$this->baseUrl}/users/me");

            if (!$response->successful()) {
                Log::error('Asana connection test failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return false;
            }

            Log::info('Asana connection test successful', [
                'user' => $response->json()['data']['name']
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error testing Asana connection', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
