<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Ticket;
use Pterodactyl\Models\TicketReply;
use Pterodactyl\Exceptions\DisplayException;

class TicketController extends ClientApiController
{
    /**
     * List all tickets for the authenticated user (newest first).
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::where('user_id', $request->user()->id)
            ->withCount('replies')
            ->orderByDesc('created_at')
            ->paginate(20);

        return new JsonResponse([
            'data' => $tickets->map(fn (Ticket $t) => $this->transformSummary($t)),
            'meta' => [
                'total'        => $tickets->total(),
                'current_page' => $tickets->currentPage(),
                'last_page'    => $tickets->lastPage(),
            ],
        ]);
    }

    /**
     * Create a new support ticket.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'    => 'required|string|max:191',
            'content'  => 'required|string|max:65535',
            'priority' => 'sometimes|string|in:low,normal,high,urgent',
        ]);

        $ticket = Ticket::create([
            'user_id'  => $request->user()->id,
            'title'    => $data['title'],
            'content'  => $data['content'],
            'priority' => $data['priority'] ?? Ticket::PRIORITY_NORMAL,
            'status'   => Ticket::STATUS_OPEN,
        ]);

        return new JsonResponse(['ticket' => $this->transformSummary($ticket)], 201);
    }

    /**
     * View a single ticket with all its replies.
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->with(['replies' => fn ($q) => $q->with('user')->orderBy('created_at')])
            ->firstOrFail();

        return new JsonResponse(['ticket' => $this->transformDetail($ticket)]);
    }

    /**
     * Add a reply to an existing ticket.
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function reply(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($ticket->status === Ticket::STATUS_CLOSED) {
            throw new DisplayException('工单已关闭，无法回复。');
        }

        $data = $request->validate(['content' => 'required|string|max:65535']);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'content'   => $data['content'],
            'is_staff'  => false,
        ]);

        $ticket->update([
            'last_reply_at' => now(),
            'status'        => Ticket::STATUS_OPEN,
        ]);

        return new JsonResponse(['success' => true]);
    }

    /**
     * Close a ticket.
     */
    public function close(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $ticket->update(['status' => Ticket::STATUS_CLOSED]);

        return new JsonResponse(['success' => true]);
    }

    private function transformSummary(Ticket $t): array
    {
        return [
            'id'            => $t->id,
            'title'         => $t->title,
            'status'        => $t->status,
            'priority'      => $t->priority,
            'replies_count' => $t->replies_count ?? 0,
            'last_reply_at' => $t->last_reply_at?->toIso8601String(),
            'created_at'    => $t->created_at?->toIso8601String(),
        ];
    }

    private function transformDetail(Ticket $t): array
    {
        return array_merge($this->transformSummary($t), [
            'content' => $t->content,
            'replies' => $t->replies->map(fn (TicketReply $r) => [
                'id'         => $r->id,
                'content'    => $r->content,
                'is_staff'   => $r->is_staff,
                'user_name'  => $r->user?->username ?? '—',
                'created_at' => $r->created_at?->toIso8601String(),
            ])->all(),
        ]);
    }
}
