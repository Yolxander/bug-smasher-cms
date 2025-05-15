<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        return $team->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        return $team->members()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        return $team->members()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();
    }

    /**
     * Determine whether the user can add members to the team.
     */
    public function addMember(User $user, Team $team): bool
    {
        return $team->members()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();
    }

    /**
     * Determine whether the user can remove members from the team.
     */
    public function removeMember(User $user, Team $team, TeamMember $member): bool
    {
        // Only admins can remove members
        if (!$team->members()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists()) {
            return false;
        }

        // Cannot remove the last admin
        if ($member->role === 'admin' &&
            $team->members()->where('role', 'admin')->count() <= 1) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update member roles.
     */
    public function updateMemberRole(User $user, Team $team, TeamMember $member): bool
    {
        // Only admins can update roles
        if (!$team->members()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists()) {
            return false;
        }

        // Cannot change the role of the last admin
        if ($member->role === 'admin' &&
            $team->members()->where('role', 'admin')->count() <= 1) {
            return false;
        }

        return true;
    }
}
