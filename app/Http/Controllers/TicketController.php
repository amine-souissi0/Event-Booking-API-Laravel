<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; // <-- add this

class TicketController extends Controller
{
    // GET /api/events/{event}/tickets  (any authed user)
    public function index(Event $event)
    {
        $tickets = $event->tickets()->orderBy('price')->get();
        return response()->json([
            'event_id' => $event->id,
            'tickets'  => $tickets,
        ]);
    }

    // POST /api/events/{event}/tickets  (organizer/admin)
    public function store(Request $request, Event $event)
    {
        $user = $request->user();

        // organizers can only manage their own event's tickets
        if ($user->role !== 'admin' && (int) $event->created_by !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'type'     => ['required','string','max:100'],   // e.g. VIP, Standard
            'price'    => ['required','numeric','min:0'],
            'quantity' => ['required','integer','min:1'],
        ]);

        $data['event_id'] = $event->id;

        $ticket = Ticket::create($data);

        // 🔄 bust cached /events lists (they may include ticket summaries later)
        Cache::flush();

        return response()->json($ticket, 201);
    }

    // GET /api/tickets/{ticket}  (any authed user)
    public function show(Ticket $ticket)
    {
        $ticket->load('event:id,title,created_by');
        return response()->json($ticket);
    }

    // PUT /api/tickets/{ticket}  (organizer/admin)
    public function update(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // load event to check ownership
        $ticket->load('event');

        if ($user->role !== 'admin' && (int) $ticket->event->created_by !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'type'     => ['sometimes','string','max:100'],
            'price'    => ['sometimes','numeric','min:0'],
            'quantity' => ['sometimes','integer','min:1'],
        ]);

        $ticket->update($data);

        // 🔄 bust cache after update
        Cache::flush();

        return response()->json($ticket);
    }

    // DELETE /api/tickets/{ticket}  (organizer/admin)
    public function destroy(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        $ticket->load('event');

        if ($user->role !== 'admin' && (int) $ticket->event->created_by !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $ticket->delete();

        // 🔄 bust cache after delete
        Cache::flush();

        return response()->json(['message' => 'Ticket deleted']);
    }
}
