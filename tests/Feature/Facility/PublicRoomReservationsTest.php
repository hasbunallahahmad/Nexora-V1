<?php

use App\Shared\Enums\ReservationStatus;
use App\Facility\Models\Room;
use App\Facility\Models\RoomReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

// beforeEach(function () {
//     Http::fake([
//         'challenges.cloudflare.com/*' => Http::response(['success' => true]),
//     ]);
// });

it('allows a guest to submit a room reservation without an account', function () {
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => true]),
    ]);

    $room = Room::factory()->create();

    $response = $this->post(route('room-reservation.store'), [
        'room_id'               => $room->id,
        'title'                 => 'Rapat koordinasi lintas instansi',
        'start_datetime'        => now()->addDay()->toDateTimeString(),
        'end_datetime'          => now()->addDay()->addHours(2)->toDateTimeString(),
        'guest_name'            => 'Budi Santoso',
        'guest_contact'         => '081234567890',
        'guest_instansi'        => 'Dinas Lingkungan Hidup',
        'website'               => '',
        'cf-turnstile-response' => 'test-token',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('reservation_success');

    $this->assertDatabaseHas('room_reservations', [
        'room_id'    => $room->id,
        'guest_name' => 'Budi Santoso',
        'status'     => ReservationStatus::Submitted->value,
    ]);
});

it('rejects submission when honeypot field is filled', function () {

    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => true]),
    ]);

    $room = Room::factory()->create();

    $response = $this->post(route('room-reservation.store'), [
        'room_id'               => $room->id,
        'title'                 => 'Rapat spam bot',
        'start_datetime'        => now()->addDay()->toDateTimeString(),
        'end_datetime'          => now()->addDay()->addHours(2)->toDateTimeString(),
        'guest_name'            => 'Bot',
        'guest_contact'         => 'bot@bot.com',
        'website'               => 'http://spam.com',
        'cf-turnstile-response' => 'test-token',
    ]);

    $response->assertSessionHasErrors('website');
});

it('rejects submission when turnstile verification fails', function () {
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => false]),
    ]);

    $room = Room::factory()->create();

    $response = $this->post(route('room-reservation.store'), [
        'room_id'               => $room->id,
        'title'                 => 'Rapat gagal verifikasi',
        'start_datetime'        => now()->addDay()->toDateTimeString(),
        'end_datetime'          => now()->addDay()->addHours(2)->toDateTimeString(),
        'guest_name'            => 'Budi',
        'guest_contact'         => '0812',
        'cf-turnstile-response' => 'bad-token',
    ]);

    $response->assertSessionHasErrors('cf-turnstile-response');
});

it('shows a conflict error when the room is already reserved for that time', function () {
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => true]),
    ]);

    $room = Room::factory()->create();

    RoomReservation::factory()->create([
        'room_id'        => $room->id,
        'status'         => ReservationStatus::Approved,
        'start_datetime' => now()->addDay(),
        'end_datetime'   => now()->addDay()->addHours(2),
    ]);

    $response = $this->post(route('room-reservation.store'), [
        'room_id'               => $room->id,
        'title'                 => 'Rapat bentrok',
        'start_datetime'        => now()->addDay()->addHour()->toDateTimeString(),
        'end_datetime'          => now()->addDay()->addHours(3)->toDateTimeString(),
        'guest_name'            => 'Budi',
        'guest_contact'         => '081234567890',
        'cf-turnstile-response' => 'test-token',
    ]);

    $response->assertSessionHasErrors('conflict');
});
