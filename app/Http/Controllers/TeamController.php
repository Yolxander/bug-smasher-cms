<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        $this->authorize('view', $team);
        return response()->json($team->load(['creator', 'members.user', 'pendingInvitations']));
    }

    /**
     * Update the specified resource in storage.
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

        $team->update($request->all());
        return response()->json($team->fresh(['creator', 'members.user']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);
        $team->delete();
        return response()->json(null, 204);
    }

    public function invite(Request $request, Team $team)
    {
        $this->authorize('invite', $team);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'role' => 'required|in:admin,member,viewer'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'email' => $request->email,
            'invited_by' => Auth::id(),
            'role' => $request->role,
            'invitation_token' => Str::random(32),
            'expires_at' => now()->addDays(7)
        ]);

        // TODO: Send invitation email

        return response()->json($invitation, 201);
    }

    public function acceptInvitation(Request $request, $token)
    {
        $invitation = TeamInvitation::where('invitation_token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            return response()->json(['message' => 'Invitation has expired'], 400);
        }

        $member = TeamMember::create([
            'team_id' => $invitation->team_id,
            'user_id' => Auth::id(),
            'role' => $invitation->role,
            'status' => 'active',
            'joined_at' => now(),
            'invited_at' => $invitation->created_at,
            'invited_by' => $invitation->invited_by
        ]);

        $invitation->accept();

        return response()->json($member->load('team'));
    }

    public function declineInvitation(Request $request, $token)
    {
        $invitation = TeamInvitation::where('invitation_token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $invitation->decline();

        return response()->json(['message' => 'Invitation declined']);
    }

    public function removeMember(Team $team, TeamMember $member)
    {
        $this->authorize('removeMember', [$team, $member]);
        $member->delete();
        return response()->json(null, 204);
    }

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
        return response()->json($member);
    }
}
