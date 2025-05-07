<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'user_id',
        'role',
        'status',
        'joined_at',
        'invited_at',
        'invited_by',
        'invitation_token',
        'invitation_expires_at',
        'metadata'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'invited_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isMember()
    {
        return $this->role === 'member';
    }

    public function isViewer()
    {
        return $this->role === 'viewer';
    }
}
