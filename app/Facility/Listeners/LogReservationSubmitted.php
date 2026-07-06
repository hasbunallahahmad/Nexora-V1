<?php

declare(strict_types=1);

namespace App\Facility\Listeners;

use App\Facility\Events\ReservationSubmitted;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder notifikasi ke approver — saat ini hanya logging.
 * Channel notifikasi nyata (email/database/Filament) menunggu konfirmasi.
 */
final class LogReservationSubmitted
{
    public function handle(ReservationSubmitted $event): void
    {
        Log::info('Room reservation submitted, awaiting approval', [
            'reservation_id' => $event->reservation->id,
            'room_id'        => $event->reservation->room_id,
            'requested_by'   => $event->reservation->requested_by,
        ]);
    }
}
