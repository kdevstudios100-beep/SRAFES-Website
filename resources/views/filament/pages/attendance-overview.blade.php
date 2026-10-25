<x-filament-panels::page>
    <x-filament::section>
        <label class="block max-w-xs">
            <span class="text-sm font-medium text-gray-950 dark:text-white">Date</span>
            <x-filament::input.wrapper class="mt-1">
                <x-filament::input type="date" wire:model.live="date" max="{{ today()->toDateString() }}" />
            </x-filament::input.wrapper>
        </label>

        @if (! $this->session)
            <p class="mt-4 text-sm text-danger-600 dark:text-danger-400">No academic session covers this date.</p>
        @endif
    </x-filament::section>

    @if ($this->session)
        @php
            $taken = $this->rows->filter(fn ($row) => $row['marked'] >= $row['on_roll'])->count();
        @endphp

        <x-filament::section>
            <x-slot name="heading">
                {{ \Illuminate\Support\Carbon::parse($date)->format('l, j F Y') }} &middot; {{ $this->session->name }}
            </x-slot>
            <x-slot name="description">
                {{ $taken }} of {{ $this->rows->count() }} {{ str('register')->plural($this->rows->count()) }} taken.
            </x-slot>

            @if ($this->rows->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No classes to show. Teachers see only the classes they are class teacher of.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <tr>
                                <th class="py-2 pe-4 font-medium">Class / section</th>
                                <th class="py-2 pe-4 font-medium">On roll</th>
                                <th class="py-2 pe-4 font-medium">Status</th>
                                <th class="py-2 pe-4 font-medium">Present</th>
                                <th class="py-2 pe-4 font-medium">Absent</th>
                                <th class="py-2 pe-4 font-medium">Late</th>
                                <th class="py-2 pe-4 font-medium">Leave</th>
                                <th class="py-2 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($this->rows as $row)
                                <tr wire:key="group-{{ $row['group']->key() }}">
                                    <td class="py-2 pe-4 font-medium text-gray-950 dark:text-white">{{ $row['group']->label() }}</td>
                                    <td class="py-2 pe-4">{{ $row['on_roll'] }}</td>
                                    <td class="py-2 pe-4">
                                        @if ($row['marked'] === 0)
                                            <x-filament::badge color="gray">Not taken</x-filament::badge>
                                        @elseif ($row['marked'] < $row['on_roll'])
                                            <x-filament::badge color="warning">Incomplete</x-filament::badge>
                                        @else
                                            <x-filament::badge color="success">Taken</x-filament::badge>
                                        @endif
                                    </td>
                                    <td class="py-2 pe-4">{{ $row['counts']['present'] }}</td>
                                    <td class="py-2 pe-4 {{ $row['counts']['absent'] ? 'font-semibold text-danger-600 dark:text-danger-400' : '' }}">{{ $row['counts']['absent'] }}</td>
                                    <td class="py-2 pe-4">{{ $row['counts']['late'] }}</td>
                                    <td class="py-2 pe-4">{{ $row['counts']['leave'] }}</td>
                                    <td class="py-2 text-end">
                                        <x-filament::link :href="$this->takeUrl($row['group']->key())" icon="heroicon-m-pencil-square">
                                            {{ $row['marked'] ? 'Open' : 'Take' }}
                                        </x-filament::link>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
