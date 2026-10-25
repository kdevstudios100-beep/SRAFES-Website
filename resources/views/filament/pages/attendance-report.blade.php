<x-filament-panels::page>
    <x-filament::section>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="block">
                <span class="text-sm font-medium text-gray-950 dark:text-white">Session</span>
                <x-filament::input.wrapper class="mt-1">
                    <x-filament::input.select wire:model.live="session">
                        @foreach ($this->sessions as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-gray-950 dark:text-white">Class / section</span>
                <x-filament::input.wrapper class="mt-1">
                    <x-filament::input.select wire:model.live="group" :disabled="$this->groups->isEmpty()">
                        @forelse ($this->groups as $option)
                            <option value="{{ $option->key() }}">{{ $option->label() }}</option>
                        @empty
                            <option value="">No classes available</option>
                        @endforelse
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-gray-950 dark:text-white">From</span>
                <x-filament::input.wrapper class="mt-1">
                    <x-filament::input type="date" wire:model.live="from" />
                </x-filament::input.wrapper>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-gray-950 dark:text-white">To</span>
                <x-filament::input.wrapper class="mt-1">
                    <x-filament::input type="date" wire:model.live="to" />
                </x-filament::input.wrapper>
            </label>
        </div>
    </x-filament::section>

    @if ($this->register)
        <x-filament::section>
            <x-slot name="heading">{{ $this->register->label() }} &middot; {{ $this->register->session->name }}</x-slot>
            <x-slot name="description">
                Attendance % = (present + late) &divide; (days marked &minus; leave). Below {{ $this->lowThreshold() }}% is highlighted.
            </x-slot>

            @if ($this->rows->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No attendance has been marked in this date range.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <tr>
                                <th class="py-2 pe-4 font-medium">Roll</th>
                                <th class="py-2 pe-4 font-medium">Student</th>
                                @foreach ($this->statuses() as $status)
                                    <th class="py-2 pe-4 font-medium">{{ $status->getLabel() }}</th>
                                @endforeach
                                <th class="py-2 pe-4 font-medium">Days</th>
                                <th class="py-2 font-medium">Attendance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($this->rows as $row)
                                <tr wire:key="student-{{ $row['enrollment']->id }}" @class(['bg-danger-50 dark:bg-danger-400/10' => $row['low']])>
                                    <td class="py-2 pe-4 text-gray-500 dark:text-gray-400">{{ $row['enrollment']->roll_number ?? '-' }}</td>
                                    <td class="py-2 pe-4">
                                        <div class="font-medium text-gray-950 dark:text-white">{{ $row['enrollment']->student->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $row['enrollment']->student->admission_number }}</div>
                                    </td>
                                    @foreach ($this->statuses() as $status)
                                        <td class="py-2 pe-4">{{ $row['counts'][$status->value] }}</td>
                                    @endforeach
                                    <td class="py-2 pe-4">{{ $row['days'] }}</td>
                                    <td @class(['py-2 font-semibold', 'text-danger-600 dark:text-danger-400' => $row['low']])>
                                        {{ $row['percent'] === null ? '-' : $row['percent'].'%' }}
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
