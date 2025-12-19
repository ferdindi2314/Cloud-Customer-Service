<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Firebase\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class TicketCommentController extends Controller
{
    public function __construct(private readonly TicketService $ticketService) {}

    public function store(Request $request, string $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $ticketData = $this->ticketService->getTicket($ticket);
        if (!$ticketData) {
            abort(404);
        }

        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // customer only on their own ticket; agent/admin can comment on any
        if ((string)($ticketData['customer_id'] ?? '') !== (string)$user->id && !in_array($user->role, ['admin', 'agent'])) {
            abort(403);
        }

        $this->ticketService->addComment($ticket, [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $user->role,
            'message' => $request->string('message')->toString(),
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Komentar berhasil dikirim');
    }
}
