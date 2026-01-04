<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TICKET COMMENT MODEL - Model untuk komentar di dalam ticket
 * 
 * PENJELASAN KE DOSEN:
 * Comment digunakan untuk komunikasi 2 arah antara Customer dan Agent/Admin.
 * Setiap update progress, pertanyaan, atau jawaban disimpan sebagai comment.
 * 
 * FIELD PENTING:
 * - firebase_id: ID di Firestore (untuk real-time sync)
 * - ticket_id: ID ticket yang dikomentari (foreign key)
 * - user_id: User yang membuat comment (foreign key ke users)
 * - comment: Isi komentar/pesan
 * - attachments: File pendukung (foto bukti, dokumen) dalam format JSON array
 * - is_internal: Boolean - apakah comment hanya untuk internal (admin/agent) atau publik (terlihat customer)
 * 
 * USE CASE:
 * - Customer bertanya: "Kapan selesai?"
 * - Agent jawab: "Sedang proses, butuh sparepart dari supplier"
 * - Agent upload foto bukti setelah selesai
 * - Admin tambah catatan internal (tidak terlihat customer)
 */
class TicketComment extends Model
{
    /**
     * Field yang boleh diisi secara mass-assignment
     */
    protected $fillable = [
        'firebase_id',
        'ticket_id',
        'user_id',
        'comment',
        'attachments',
        'is_internal',
    ];

    /**
     * Cast otomatis tipe data
     * - attachments jadi array saat diakses
     * - is_internal jadi boolean (0/1 -> false/true)
     * - timestamps jadi Carbon instance
     */
    protected $casts = [
        'attachments' => 'array',
        'is_internal' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Relasi ke Ticket
     * Comment belongsTo Ticket (1 comment milik 1 ticket)
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relasi ke User
     * Comment belongsTo User (1 comment dibuat oleh 1 user)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope untuk filter hanya comment public (terlihat customer)
     * Contoh: TicketComment::public()->get()
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope untuk filter hanya comment internal (admin/agent only)
     * Contoh: TicketComment::internal()->get()
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Cek apakah comment ini internal
     * @return bool
     */
    public function isInternal(): bool
    {
        return $this->is_internal;
    }

    /**
     * Cek apakah comment ini public (terlihat customer)
     * @return bool
     */
    public function isPublic(): bool
    {
        return !$this->is_internal;
    }
}
