<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Firebase\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

/**
 * TICKET COMMENT CONTROLLER - Controller untuk mengelola komentar di ticket
 * 
 * PENJELASAN KE DOSEN:
 * Controller ini handle fitur komunikasi 2 arah antara Customer dan Agent/Admin.
 * Setiap pihak bisa menambahkan komentar untuk update progress atau bertanya.
 * 
 * FLOW KOMENTAR:
 * 1. User (Customer/Agent/Admin) tulis komentar di form
 * 2. Klik "Kirim Komentar"
 * 3. Controller validasi & simpan ke database + Firestore
 * 4. Redirect kembali ke halaman ticket dengan pesan sukses
 * 5. Komentar langsung terlihat oleh semua pihak (real-time via Firestore)
 * 
 * PERMISSION:
 * - Customer: Hanya bisa comment di ticket mereka sendiri
 * - Agent: Bisa comment di ticket yang di-assign ke mereka
 * - Admin: Bisa comment di semua ticket
 */
class TicketCommentController extends Controller
{
    /**
     * Constructor - inject TicketService untuk akses Firestore
     * 
     * @param TicketService $ticketService - Service untuk CRUD operations
     */
    public function __construct(private readonly TicketService $ticketService) {}

    /**
     * STORE - Simpan komentar baru
     * 
     * @param Request $request - HTTP request dengan data form
     * @param string $ticket - Firebase ID dari ticket yang dikomentari
     * @return \Illuminate\Http\RedirectResponse
     * 
     * ALUR:
     * 1. Validasi input (komentar wajib diisi, max 2000 karakter)
     * 2. Cek apakah ticket ada di database
     * 3. Cek permission user (boleh comment atau tidak)
     * 4. Simpan komentar ke Firestore + Laravel DB
     * 5. Redirect ke halaman ticket dengan flash message sukses
     */
    public function store(Request $request, string $ticket)
    {
        // STEP 1: Validasi input
        // Field 'message' wajib diisi, bertipe string, maksimal 2000 karakter
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // STEP 2: Cek apakah ticket ada
        // Ambil data ticket dari Firestore/Laravel DB via TicketService
        $ticketData = $this->ticketService->getTicket($ticket);
        if (!$ticketData) {
            abort(404); // Ticket tidak ditemukan, return 404 Not Found
        }

        // STEP 3: Ambil data user yang sedang login
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login'); // Belum login, redirect ke login
        }

        // STEP 4: Cek permission
        // Customer hanya boleh comment di ticket mereka sendiri
        // Agent/Admin boleh comment di semua ticket
        $isOwner = (string)($ticketData['customer_id'] ?? '') === (string)$user->id;
        $isAdminOrAgent = in_array($user->role, ['admin', 'agent']);
        
        if (!$isOwner && !$isAdminOrAgent) {
            abort(403); // Forbidden - tidak punya akses
        }

        // STEP 5: Simpan komentar
        // PENTING: Field database name-nya 'comment', tapi form field-nya 'message'
        // Ini untuk konsistensi dengan database schema yang sudah ada
        $this->ticketService->addComment($ticket, [
            'user_id' => $user->id,           // ID user yang comment
            'user_name' => $user->name,       // Nama user (untuk tampilan)
            'role' => $user->role,            // Role user (customer/agent/admin)
            'comment' => $request->string('message')->toString(), // Isi komentar
        ]);

        // STEP 6: Redirect dengan pesan sukses
        // Flash message 'success' akan ditampilkan di view
        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Komentar berhasil dikirim');
    }
}
