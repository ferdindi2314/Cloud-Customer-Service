<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComment extends Model
{
    protected $fillable = [
        'firebase_id',
        'ticket_id',
        'user_id',
        'comment',
        'attachments',
        'is_internal',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_internal' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    // Helper methods
    public function isInternal(): bool
    {
        return $this->is_internal;
    }

    public function isPublic(): bool
    {
        return !$this->is_internal;
    }
}
