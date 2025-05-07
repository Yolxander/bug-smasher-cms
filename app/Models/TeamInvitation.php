<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeamInvitation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'email',
        'invited_by',
        'role',
        'status',
        'invitation_token',
        'expires_at',
        'responded_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    // Helper methods
    public function isExpired()
    {
        return $this->expires_at->isPast() || $this->status === 'expired';
    }

    public function isPending()
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function accept()
    {
        $this->update([
            'status' => 'accepted',
            'responded_at' => now()
        ]);
    }

    public function decline()
    {
        $this->update([
            'status' => 'declined',
            'responded_at' => now()
        ]);
    }
}
