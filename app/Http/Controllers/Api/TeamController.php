<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeamController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of teams.
     */
    public function index()
    {
        $teams = Team::with(['creator', 'members.user'])
            ->whereHas('members', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->paginate(10);

        return response()->json($teams);
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,archived',
            'avatar_url' => 'nullable|url',
            'settings' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $team = Team::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => Auth::id(),
            'slug' => Str::slug($request->name),
            'avatar_url' => $request->avatar_url,
            'settings' => $request->settings
        ]);

        // Add creator as admin
        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => Auth::id(),
            'role' => 'admin',
            'status' => 'active',
            'joined_at' => now(),
            'invited_at' => now(),
            'invited_by' => Auth::id()
        ]);

        return response()->json($team->load(['creator', 'members.user']), 201);
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team)
    {
        $this->authorize('view', $team);
        return response()->json($team->load(['creator', 'members.user']));
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:active,inactive,archived',
            'avatar_url' => 'nullable|url',
            'settings' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) {
            $request->merge(['slug' => Str::slug($request->name)]);
        }

        $team->update($request->all());
        return response()->json($team->fresh(['creator', 'members.user']));
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);
        $team->delete();
        return response()->json(null, 204);
    }

    /**
     * Add a member to the team.
     */
    public function addMember(Request $request, Team $team)
    {
        $this->authorize('addMember', $team);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,member,viewer',
            'status' => 'required|in:active,pending,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $member = TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $request->user_id,
            'role' => $request->role,
            'status' => $request->status,
            'joined_at' => $request->status === 'active' ? now() : null,
            'invited_at' => now(),
            'invited_by' => Auth::id(),
            'invitation_token' => Str::random(32),
            'invitation_expires_at' => now()->addDays(7)
        ]);

        return response()->json($member->load('user'), 201);
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Team $team, TeamMember $member)
    {
        $this->authorize('removeMember', [$team, $member]);
        $member->delete();
        return response()->json(null, 204);
    }

    /**
     * Update a member's role.
     */
    public function updateMemberRole(Request $request, Team $team, TeamMember $member)
    {
        $this->authorize('updateMemberRole', [$team, $member]);

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,member,viewer'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $member->update(['role' => $request->role]);
        return response()->json($member->load('user'));
    }

    /**
     * Get all members of a team.
     */
    public function getMembers(Team $team)
    {
        $this->authorize('view', $team);
        return response()->json($team->members()->with('user')->paginate(10));
    }

    /**
     * Get teams by user ID.
     */
    public function getTeamsByMemberId($userId)
    {
        Log::info('Getting teams for user ID: ' . $userId, [
            'auth_user_id' => Auth::id(),
            'request_time' => now()->toDateTimeString()
        ]);

        try {
            // Check if the authenticated user has permission to view this user's teams
            $isAuthorized = Auth::id() == $userId || Auth::user()->isAdminOf(Team::whereHas('members', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->first());

            Log::info('Authorization check', [
                'is_authorized' => $isAuthorized,
                'is_self' => Auth::id() == $userId,
                'requested_user_id' => $userId
            ]);

            if (!$isAuthorized) {
                Log::warning('Unauthorized access attempt', [
                    'requested_user_id' => $userId,
                    'auth_user_id' => Auth::id()
                ]);
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $teams = Team::with(['creator', 'members.user'])
                ->whereHas('members', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->get();

            Log::info('Teams retrieved successfully', [
                'user_id' => $userId,
                'teams_count' => $teams->count(),
                'team_ids' => $teams->pluck('id')->toArray()
            ]);

            return response()->json($teams);
        } catch (\Exception $e) {
            Log::error('Error getting teams by user ID', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
