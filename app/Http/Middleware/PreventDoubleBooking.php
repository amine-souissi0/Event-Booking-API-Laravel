<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use App\Models\Ticket;
use Closure;
use Illuminate\Http\Request;

class PreventDoubleBooking
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $ticketId = (int) $request->input('ticket_id');
        $qty      = (int) $request->input('quantity', 1);

        if (!$ticketId || $qty < 1) {
            return response()->json(['message' => 'ticket_id and valid quantity are required'], 422);
        }

        $ticket = Ticket::find($ticketId);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        // 1) block duplicate booking for same user+ticket (pending/confirmed)
        $already = Booking::where('user_id', $user->id)
            ->where('ticket_id', $ticketId)
            ->whereIn('status', ['pending','confirmed'])
            ->exists();

        if ($already) {
            return response()->json(['message' => 'You already have a booking for this ticket'], 409);
        }

        // 2) stock check: sum all non-cancelled booked qty for this ticket
        $booked = Booking::where('ticket_id', $ticketId)
            ->whereIn('status', ['pending','confirmed'])
            ->sum('quantity');

        $remaining = (int) $ticket->quantity - (int) $booked;

        if ($qty > $remaining) {
            return response()->json([
                'message'   => 'Not enough stock',
                'remaining' => $remaining,
            ], 409);
        }

        return $next($request);
    }
}
