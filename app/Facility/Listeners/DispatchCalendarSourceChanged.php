<?php

declare(strict_types=1);

namespace App\Facility\Listeners;

use App\Calendar\Events\CalendarSourceChanged;

/**
 * Satu listener generik untuk keempat event reservasi — setiap perubahan
 * status (Submitted/Approved/Rejected/Cancelled) berpotensi mengubah apa
 * yang tampil di Calendar (Admin melihat Submitted, Public hanya melihat
 * Approved/Completed), sehingga wajib invalidate cache di keempatnya.
 */
final class DispatchCalendarSourceChanged
{
    public function handle(object $event): void
    {
        event(new CalendarSourceChanged('room_reservation'));
    }
}
