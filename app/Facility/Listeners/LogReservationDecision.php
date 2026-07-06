<?php

declare(strict_types=1);

namespace App\Facility\Listeners;

use App\Facility\Events\ReservationApproved;
use App\Facility\Events\ReservationRejected;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder notifikasi ke requester saat reservasi diputuskan.
 * Channel notifikasi nyata menunggu konfirmasi.
 */
final class LogReservationDecision
{
    public function handle(ReservationApproved|ReservationRejected $event): void
    {
        Log::info('Room reservation decision made', [
            'reservation_id' => $event->reservation->id,
            'status'         => $event->reservation->status->value,
        ]);
    }
}
