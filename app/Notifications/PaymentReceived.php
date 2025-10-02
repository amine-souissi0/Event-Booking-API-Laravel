<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // 
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification implements ShouldQueue //
{
    use Queueable;

    public function __construct(public Booking $booking)
    {
        // (optional) put this notification on a specific queue:
        // $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        // Database channel only; add 'mail' later if SMTP is configured
        return ['database'];
    }

    // Use toDatabase for the database channel payload
    public function toDatabase($notifiable): array
    {
        // Ensure relations are available (in case serialized across queue)
        $this->booking->loadMissing('ticket.event');

        return [
            'message'    => 'Payment received for booking #'.$this->booking->id,
            'booking_id' => (int) $this->booking->id,
            'amount'     => (float) $this->booking->ticket->price * (int) $this->booking->quantity,
            'event'      => optional($this->booking->ticket->event)->title,
            'user_id'    => (int) $this->booking->user_id,
            'ticket_id'  => (int) $this->booking->ticket_id,
            'quantity'   => (int) $this->booking->quantity,
            'status'     => $this->booking->status,
        ];
    }
}
