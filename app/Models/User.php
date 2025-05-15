<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function qaChecklistAssignments()
    {
        return $this->hasMany(QaChecklistAssignment::class);
    }

    public function assignedQaChecklists()
    {
        return $this->hasMany(QaChecklistAssignment::class, 'assigned_by');
    }

    /**
     * Get the teams that the user is a member of.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get the teams created by the user.
     */
    public function createdTeams()
    {
        return $this->hasMany(Team::class, 'created_by');
    }

    /**
     * Get the team memberships of the user.
     */
    public function teamMemberships()
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Get the teams where the user is an admin.
     */
    public function adminTeams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->wherePivot('role', 'admin')
            ->withPivot(['status', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Check if the user is a member of a specific team.
     */
    public function isMemberOf(Team $team): bool
    {
        return $this->teams()->where('teams.id', $team->id)->exists();
    }

    /**
     * Check if the user is an admin of a specific team.
     */
    public function isAdminOf(Team $team): bool
    {
        return $this->adminTeams()->where('teams.id', $team->id)->exists();
    }
}
