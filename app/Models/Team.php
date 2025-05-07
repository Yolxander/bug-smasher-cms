<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'status',
        'slug',
        'avatar_url',
        'settings'
    ];

    protected $casts = [
        'settings' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function invitations()
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function activeMembers()
    {
        return $this->members()->where('status', 'active');
    }

    public function pendingInvitations()
    {
        return $this->invitations()->where('status', 'pending');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }
}
