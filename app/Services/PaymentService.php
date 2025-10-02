<?php

namespace App\Services;

use App\Models\Booking;

class PaymentService
{
    /**
     * Mock charge: returns ['ok' => bool, 'txn' => string]
     * You could add logic like failing when amount > X, etc.
     */
    public function charge(Booking $booking, float $amount): array
    {
        // simple deterministic "success"
        $ok = true;
        $txn = 'TXN-' . strtoupper(bin2hex(random_bytes(5)));

        return ['ok' => $ok, 'txn' => $txn];
    }
}
