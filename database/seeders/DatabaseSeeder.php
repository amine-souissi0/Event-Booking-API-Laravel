<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Users
        $admins = User::factory(2)->admin()->create([
            'password' => Hash::make('password'),
        ]);

        $organizers = User::factory(3)->organizer()->create([
            'password' => Hash::make('password'),
        ]);

        $customers = User::factory(10)->create([
            'role'     => User::ROLE_CUSTOMER,
            'password' => Hash::make('password'),
        ]);

        // 2) Events (5 total), assigned to existing organizers
        $events = collect();
        for ($i = 0; $i < 5; $i++) {
            $events->push(
                Event::factory()->create([
                    'created_by' => $organizers->random()->id,
                ])
            );
        }

        // 3) Tickets: 3 per event (15 total), all tied to existing events
        $tickets = collect();
        foreach ($events as $event) {
            $tickets = $tickets->merge(
                Ticket::factory()->count(3)->create([
                    'event_id' => $event->id,
                ])
            );
        }

        // 4) Bookings: 20 — IMPORTANT: create them manually against EXISTING users & tickets
        $bookings = collect();
        for ($i = 0; $i < 20; $i++) {
            $ticket   = $tickets->random();
            $customer = $customers->random();
            $qty      = rand(1, 5);

            $booking = Booking::create([
                'user_id'   => $customer->id,
                'ticket_id' => $ticket->id,
                'quantity'  => $qty,
                'status'    => Booking::STATUS_CONFIRMED, // or random if you want
            ]);

            $bookings->push($booking);
        }

        // 5) Payments: one per booking (amount = ticket price * qty)
        foreach ($bookings as $booking) {
            $amount = $booking->ticket->price * $booking->quantity;

            Payment::create([
                'booking_id' => $booking->id,
                'amount'     => $amount,
                'status'     => Payment::STATUS_SUCCESS,
            ]);
        }
    }
}
