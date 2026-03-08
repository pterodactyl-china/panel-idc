<?php

namespace Pterodactyl\Http\Controllers\Admin\Store;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\Ticket;
use Pterodactyl\Models\TicketReply;
use Pterodactyl\Http\Controllers\Controller;

class TicketAdminController extends Controller
{
    public function __construct(private readonly AlertsMessageBag $alert)
    {
    }

    public function index(Request $request): View
    {
        $query = Ticket::query()
            ->with('user')
            ->withCount('replies')
            ->orderByDesc('created_at');

        if ($status = $request->input('filter.status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->input('filter.priority')) {
            $query->where('priority', $priority);
        }

        return view('admin.store.tickets.index', [
            'tickets' => $query->paginate(30),
        ]);
    }

    public function view(int $id): View
    {
        $ticket = Ticket::with([
            'user',
            'replies' => fn ($q) => $q->with('user')->orderBy('created_at'),
        ])->findOrFail($id);

        return view('admin.store.tickets.view', ['ticket' => $ticket]);
    }

    public function reply(Request $request, int $id): RedirectResponse
    {
        $ticket = Ticket::findOrFail($id);

        $data = $request->validate(['content' => 'required|string|max:65535']);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'content'   => $data['content'],
            'is_staff'  => true,
        ]);

        $ticket->update([
            'last_reply_at' => now(),
            'status'        => Ticket::STATUS_IN_PROGRESS,
        ]);

        $this->alert->success('回复已发送。')->flash();

        return redirect()->route('admin.store.tickets.view', $ticket->id);
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $ticket = Ticket::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|string|in:open,in_progress,closed',
        ]);

        $ticket->update(['status' => $data['status']]);

        $this->alert->success('工单状态已更新。')->flash();

        return redirect()->route('admin.store.tickets.view', $ticket->id);
    }

    public function destroy(int $id): RedirectResponse
    {
        Ticket::findOrFail($id)->delete();

        $this->alert->success('工单已删除。')->flash();

        return redirect()->route('admin.store.tickets');
    }
}
