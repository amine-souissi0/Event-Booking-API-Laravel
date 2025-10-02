<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use App\Notifications\PaymentReceived; // 
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function pay(Request $request, Booking $booking, PaymentService $payments)
    {
        $user = $request->user();

        // owner or admin
        if ($user->role !== 'admin' && (int) $booking->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // only pending bookings can be paid
        if ($booking->status !== \App\Models\Booking::STATUS_PENDING) {
            return response()->json(['message' => 'Booking is not pending'], 422);
        }

        // prevent double payment
        if ($booking->payment()->exists()) {
            return response()->json(['message' => 'Booking already paid'], 409);
        }

        // amount = ticket price * qty
        $booking->load('ticket');
        $amount = (float) $booking->ticket->price * (int) $booking->quantity;

        // call mock payment service
        $result = $payments->charge($booking, $amount);

        if (!$result['ok']) {
            return response()->json(['message' => 'Payment failed'], 402);
        }

        // record payment
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount'     => $amount,
            'status'     => Payment::STATUS_SUCCESS,
        ]);

        // update booking status
        $booking->status = \App\Models\Booking::STATUS_CONFIRMED;
        $booking->save();

        // 🔔 send notifications (customer + organizer)
        $booking->load(['ticket.event', 'user']);
        $customer  = $booking->user;
        $organizer = $booking->ticket->event?->organizer;

        $customer?->notify(new PaymentReceived($booking));
        $organizer?->notify(new PaymentReceived($booking));
        Log::info('Payment received', [
        'booking_id' => $booking->id,
        'user_id'    => $booking->user_id,
        'amount'     => (float) $booking->ticket->price * (int) $booking->quantity,
        ]);

        // return combined info
        $booking->load(['ticket.event:id,title', 'user:id,name,email']);
        return response()->json([
            'booking' => $booking,
            'payment' => $payment,
            'txn'     => $result['txn'],
        ], 201);
    }
}
