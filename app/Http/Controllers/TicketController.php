<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Services\Firebase\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;



class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index()
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (in_array($user->role, ['admin', 'agent'])) {
            $tickets = $this->ticketService->getAllTickets();
        } else {
            $tickets = $this->ticketService->getTicketsByCustomer($user->id);
        }

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('tickets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'priority'    => 'required|in:low,medium,high',
            'attachments.*' => 'nullable|file|max:2048',
        ]);

        $data = $request->only(['title', 'description', 'priority']);
        $cat = Category::find($request->input('category_id'));
        if ($cat) {
            $data['category_id'] = (string)$cat->id;
            $data['category'] = $cat->name;
        }
        $data['customer_id'] = Auth::id();

        $ticketId = $this->ticketService->createTicket($data);

        $files = $request->file('attachments', []);
        if (is_array($files) && count($files) > 0) {
            $attachments = $this->ticketService->uploadAttachments($files, $ticketId);
            if (count($attachments) > 0) {
                $this->ticketService->updateTicket($ticketId, ['attachments' => $attachments]);
            }
        }

        return redirect()->route('tickets.show', $ticketId)->with('success', 'Ticket berhasil dibuat');
    }

    public function show(string $id)
    {
        $ticket = $this->ticketService->getTicket($id);
        if (!$ticket) {
            abort(404);
        }

        $comments = $this->ticketService->getComments($id);

        // Generate temporary URLs for attachments (if any)
        if (isset($ticket['attachments']) && is_array($ticket['attachments'])) {
            foreach ($ticket['attachments'] as &$att) {
                if (isset($att['path']) && is_string($att['path'])) {
                    $att['temp_url'] = $this->ticketService->getAttachmentTemporaryUrl($id, $att['path']);
                }
            }
        }

        return view('tickets.show', compact('ticket', 'comments'));
    }

    public function edit(string $id)
    {
        $ticket = $this->ticketService->getTicket($id);
        if (!$ticket) {
            abort(404);
        }

        // Hanya pembuat atau admin/agent boleh edit (aturan bisa disesuaikan)
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        if ((string)($ticket['customer_id'] ?? '') !== (string)$user->id && !in_array($user->role, ['admin', 'agent'])) {
            abort(403);
        }

        $categories = Category::orderBy('name')->get();
        return view('tickets.edit', compact('ticket', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'priority'    => 'required|in:low,medium,high',
        ]);

        $data = $request->only(['title', 'description', 'priority']);
        $cat = Category::find($request->input('category_id'));
        if ($cat) {
            $data['category_id'] = (string)$cat->id;
            $data['category'] = $cat->name;
        }

        $this->ticketService->updateTicket($id, $data);

        return redirect()->route('tickets.show', $id)->with('success', 'Ticket berhasil diupdate');
    }

    public function destroy(string $id)
    {
        $this->ticketService->deleteTicket($id);
        return redirect()->route('tickets.index')->with('success', 'Ticket berhasil dihapus');
    }

    public function assignAgent(Request $request, string $id)
    {
        $request->validate([
            'agent_id' => 'required|string',
        ]);

        $this->ticketService->updateTicket($id, [
            'assigned_agent_id' => $request->agent_id,
        ]);

        return back()->with('success', 'Ticket berhasil di-assign ke agent');
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $this->ticketService->updateTicket($id, [
            'status' => $request->status,
        ]);

        return back()->with('success', 'Status ticket berhasil diperbarui');
    }

    public function downloadAttachment(Request $request, string $ticketId, string $path)
    {
        // Signed route middleware will validate signature if route is signed, but we also
        // verify ticket and that attachment exists in ticket metadata.
        $decodedPath = base64_decode($path);
        $ticket = $this->ticketService->getTicket($ticketId);
        if (!$ticket) {
            abort(404);
        }

        $found = null;
        if (isset($ticket['attachments']) && is_array($ticket['attachments'])) {
            foreach ($ticket['attachments'] as $att) {
                if (($att['path'] ?? null) === $decodedPath) {
                    $found = $att;
                    break;
                }
            }
        }

        if (!$found) {
            abort(404);
        }

        // permission: only customer owner, assigned agent, or admin can download
        $user = Auth::user();
        if (!$user) abort(403);

        $isOwner = ((string)($ticket['customer_id'] ?? '') === (string)$user->id);
        $isAssigned = ((string)($ticket['assigned_agent_id'] ?? '') === (string)$user->id);
        if (!($isOwner || $isAssigned || in_array($user->role, ['admin', 'agent']))) {
            abort(403);
        }

        // Serve local file
        if (($found['storage'] ?? '') === 'local') {
            $disk = config('attachments.local_disk', 'local');
            $fullPath = Storage::disk($disk)->path($found['path']);
            if (!file_exists($fullPath)) {
                abort(404);
            }

            return response()->streamDownload(function () use ($fullPath) {
                readfile($fullPath);
            }, $found['name'] ?? basename($fullPath), ['Content-Type' => $found['content_type'] ?? 'application/octet-stream']);
        }

        // If firebase storage
        if (($found['storage'] ?? '') === 'firebase') {
            $temp = $this->ticketService->getAttachmentTemporaryUrl($ticketId, $found['path']);
            if ($temp) {
                return redirect($temp);
            }
        }

        abort(404);
    }
}
