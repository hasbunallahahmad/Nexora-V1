<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Ketersediaan Kendaraan</x-slot>

        <div class="flex flex-col gap-2">
            @forelse ($vehicles as $item)
                <div class="flex items-center justify-between rounded-lg border p-3 dark:border-gray-700">
                    <div>
                        <p class="font-medium text-sm">{{ $item['vehicle']->name }} —
                            {{ $item['vehicle']->plate_number }}</p>
                        @if ($item['onMaintenance'])
                            <p class="text-xs text-gray-500">Sedang dijadwalkan perawatan</p>
                        @elseif ($item['inUse'])
                            <p class="text-xs text-gray-500">
                                {{ $item['current']->title }} — {{ $item['current']->requestedBy?->name ?? '—' }}
                                s/d {{ $item['current']->end_datetime->format('H:i') }}
                            </p>
                        @else
                            <p class="text-xs text-gray-500">Sopir: {{ $item['vehicle']->driver_name ?? '—' }}</p>
                        @endif
                    </div>
                    <span @class([
                        'px-2 py-1 rounded-full text-xs font-semibold',
                        'bg-warning-100 text-warning-700' => $item['onMaintenance'],
                        'bg-danger-100 text-danger-700' =>
                            !$item['onMaintenance'] && $item['inUse'],
                        'bg-success-100 text-success-700' =>
                            !$item['onMaintenance'] && !$item['inUse'],
                    ])>
                        {{ $item['onMaintenance'] ? 'Perawatan' : ($item['inUse'] ? 'Terpakai' : 'Tersedia') }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada kendaraan aktif.</p>
            @endforelse
        </div>

        @if ($canManage)
            <x-slot name="footer">
                <a href="{{ \App\Mobility\Filament\Resources\Vehicles\VehicleResource::getUrl() }}"
                    class="text-sm text-primary-600 hover:underline">
                    Kelola Kendaraan →
                </a>
            </x-slot>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
