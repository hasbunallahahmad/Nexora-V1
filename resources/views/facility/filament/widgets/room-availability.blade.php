<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Ketersediaan Ruangan</x-slot>

        <div class="flex flex-col gap-2">
            @forelse ($rooms as $item)
                <div class="flex items-center justify-between rounded-lg border p-3 dark:border-gray-700">
                    <div>
                        <p class="font-medium text-sm">{{ $item['room']->name }}</p>
                        @if ($item['inUse'])
                            <p class="text-xs text-gray-500">
                                {{ $item['current']->title }} — {{ $item['current']->requestedBy?->name ?? '—' }}
                                s/d {{ $item['current']->end_datetime->format('H:i') }}
                            </p>
                        @else
                            <p class="text-xs text-gray-500">Kapasitas {{ $item['room']->capacity }} orang</p>
                        @endif
                    </div>
                    <span @class([
                        'px-2 py-1 rounded-full text-xs font-semibold',
                        'bg-danger-100 text-danger-700' => $item['inUse'],
                        'bg-success-100 text-success-700' => !$item['inUse'],
                    ])>
                        {{ $item['inUse'] ? 'Terpakai' : 'Tersedia' }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada ruangan aktif.</p>
            @endforelse
        </div>

        @if ($canManage)
            <x-slot name="footer">
                <a href="{{ \App\Facility\Filament\Resources\Rooms\RoomResource::getUrl() }}"
                    class="text-sm text-primary-600 hover:underline">
                    Kelola Ruangan →
                </a>
            </x-slot>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
