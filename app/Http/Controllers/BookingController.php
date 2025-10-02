<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // GET /api/bookings
    // - Admin: list all (optional ?all=1), else only your bookings
    public function index(Request $request)
    {
        $user = $request->user();
        $q = Booking::with(['ticket.event:id,title', 'user:id,name,email']);

        if ($user->role !== 'admin' || !$request->boolean('all')) {
            $q->where('user_id', $user->id);
        }

        return response()->json(
            $q->orderBy('created_at', 'desc')->paginate(10)
        );
    }

    // GET /api/bookings/{booking}
    // - Owner or admin
    public function show(Request $request, Booking $booking)
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $booking->load(['ticket.event:id,title', 'user:id,name,email']);
        return response()->json($booking);
    }

    // POST /api/bookings  (any authed user) + middleware guards
    public function store(Request $request)
    {
        $data = $request->validate([
            'ticket_id' => ['required','integer','exists:tickets,id'],
            'quantity'  => ['required','integer','min:1'],
        ]);

        $ticket = Ticket::findOrFail($data['ticket_id']);

        $booking = Booking::create([
            'user_id'   => $request->user()->id,
            'ticket_id' => $ticket->id,
            'quantity'  => $data['quantity'],
            'status'    => Booking::STATUS_PENDING, // 
        ]);

        // include quick context in response
        $booking->load(['ticket.event:id,title', 'user:id,name,email']);

        return response()->json($booking, 201);
    }

    // DELETE /api/bookings/{booking}
    // - Owner can cancel own; admin can delete any
    public function destroy(Request $request, Booking $booking)
{
    $user = $request->user();

    if ($user->role !== 'admin' && (int) $booking->user_id !== (int) $user->id) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    // mark cancelled, then delete
    $booking->status = Booking::STATUS_CANCELLED;
    $booking->save();

    $booking->delete();

    return response()->json(['message' => 'Booking cancelled']);
}

}
