<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Menunggu Persetujuan</x-slot>

        @if ($pending->isEmpty())
            <p class="text-sm text-gray-500">Tidak ada reservasi yang menunggu persetujuan.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase text-gray-500 border-b dark:border-gray-700">
                            <th class="py-2 pr-4">Jenis</th>
                            <th class="py-2 pr-4">Judul</th>
                            <th class="py-2 pr-4">Resource</th>
                            <th class="py-2 pr-4">Diajukan Oleh</th>
                            <th class="py-2 pr-4">Waktu Mulai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pending as $item)
                            <tr class="border-b dark:border-gray-800">
                                <td class="py-2 pr-4">
                                    <a href="{{ $item['url'] }}"
                                        class="inline-flex items-center gap-1 hover:underline">
                                        <span>{{ $item['icon'] }}</span>
                                        <span>{{ $item['type'] }}</span>
                                    </a>
                                </td>
                                <td class="py-2 pr-4">
                                    <a href="{{ $item['url'] }}" class="hover:underline">{{ $item['title'] }}</a>
                                </td>
                                <td class="py-2 pr-4">{{ $item['resource'] }}</td>
                                <td class="py-2 pr-4">{{ $item['requestedBy'] }}</td>
                                <td class="py-2 pr-4">{{ $item['start']->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
