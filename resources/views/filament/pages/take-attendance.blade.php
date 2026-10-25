<x-filament-panels::page>
    <x-filament::section>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block">
                <span class="text-sm font-medium text-gray-950 dark:text-white">Date</span>
                <x-filament::input.wrapper class="mt-1">
                    <x-filament::input type="date" wire:model.live="date" max="{{ today()->toDateString() }}" />
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
        </div>

        @if (! $this->session)
            <p class="mt-4 text-sm text-danger-600 dark:text-danger-400">No academic session covers this date.</p>
        @elseif ($this->groups->isEmpty())
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                You have no classes to take attendance for in {{ $this->session->name }}. Ask the office to assign you as a class teacher.
            </p>
        @endif
    </x-filament::section>

    @if ($this->register)
        @php
            $locked = $this->lockedReason;
            $taken = $this->existing->isNotEmpty();
            $lastSaved = $this->existing->sortByDesc('updated_at')->first();
            $tally = collect($this->marks)->countBy(fn ($mark) => $mark['status'] ?? '');
        @endphp

        <x-filament::section>
            <x-slot name="heading">
                {{ $this->register->label() }} &middot; {{ \Illuminate\Support\Carbon::parse($date)->format('l, j F Y') }}
            </x-slot>
            <x-slot name="description">
                {{ $this->roster->count() }} {{ str('student')->plural($this->roster->count()) }} on roll.
                @if ($taken)
                    Taken; last saved {{ $lastSaved->updated_at->diffForHumans() }}{{ $lastSaved->recordedBy ? ' by '.$lastSaved->recordedBy->name : '' }}.
                @else
                    Not taken yet.
                @endif
            </x-slot>

            @if ($locked)
                <div class="mb-4 rounded-lg bg-warning-50 p-3 text-sm text-warning-700 dark:bg-warning-400/10 dark:text-warning-400">
                    {{ $locked }}
                </div>
            @endif

            @if ($this->roster->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No students are enrolled here on this date.</p>
            @else
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    @unless ($locked)
                        <x-filament::button size="sm" color="success" wire:click="markAll('present')" icon="heroicon-m-check">
                            Mark all present
                        </x-filament::button>
                    @endunless

                    @foreach ($this->statuses() as $status)
                        <x-filament::badge :color="$status->getColor()">
                            {{ $status->getLabel() }}: {{ $tally[$status->value] ?? 0 }}
                        </x-filament::badge>
                    @endforeach

                    @if ($tally[''] ?? 0)
                        <x-filament::badge color="gray">Not marked: {{ $tally[''] }}</x-filament::badge>
                    @endif
                </div>

                <form wire:submit="save">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-gray-200 text-gray-500 dark:border-white/10 dark:text-gray-400">
                                <tr>
                                    <th class="py-2 pe-3 font-medium">Roll</th>
                                    <th class="py-2 pe-3 font-medium">Student</th>
                                    <th class="py-2 pe-3 font-medium">Attendance</th>
                                    <th class="py-2 font-medium">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @foreach ($this->roster as $enrollment)
                                    <tr wire:key="row-{{ $enrollment->id }}">
                                        <td class="py-2 pe-3 align-middle text-gray-500 dark:text-gray-400">{{ $enrollment->roll_number ?? '-' }}</td>
                                        <td class="py-2 pe-3 align-middle">
                                            <div class="font-medium text-gray-950 dark:text-white">{{ $enrollment->student->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $enrollment->student->admission_number }}</div>
                                        </td>
                                        <td class="py-2 pe-3 align-middle">
                                            <div class="flex flex-wrap gap-1" role="radiogroup" aria-label="Attendance for {{ $enrollment->student->name }}">
                                                @foreach ($this->statuses() as $status)
                                                    <label @class([
                                                        'cursor-pointer rounded-md border px-2.5 py-1 text-xs font-semibold transition',
                                                        'pointer-events-none opacity-60' => $locked,
                                                        'border-success-600 bg-success-600 text-white' => ($marks[$enrollment->id]['status'] ?? null) === $status->value && $status->value === 'present',
                                                        'border-danger-600 bg-danger-600 text-white' => ($marks[$enrollment->id]['status'] ?? null) === $status->value && $status->value === 'absent',
                                                        'border-warning-500 bg-warning-500 text-white' => ($marks[$enrollment->id]['status'] ?? null) === $status->value && $status->value === 'late',
                                                        'border-info-600 bg-info-600 text-white' => ($marks[$enrollment->id]['status'] ?? null) === $status->value && $status->value === 'leave',
                                                        'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:text-gray-200 dark:hover:bg-white/5' => ($marks[$enrollment->id]['status'] ?? null) !== $status->value,
                                                    ]) title="{{ $status->getLabel() }}">
                                                        <input
                                                            type="radio"
                                                            class="sr-only"
                                                            value="{{ $status->value }}"
                                                            wire:model.live="marks.{{ $enrollment->id }}.status"
                                                            @disabled($locked)
                                                        />
                                                        {{ $status->getLabel() }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="py-2 align-middle">
                                            <x-filament::input.wrapper :disabled="(bool) $locked">
                                                <x-filament::input
                                                    type="text"
                                                    maxlength="255"
                                                    placeholder="Optional"
                                                    wire:model="marks.{{ $enrollment->id }}.remarks"
                                                    :disabled="(bool) $locked"
                                                />
                                            </x-filament::input.wrapper>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @unless ($locked)
                        <div class="mt-6">
                            <x-filament::button type="submit" icon="heroicon-m-check-circle">
                                {{ $taken ? 'Save changes' : 'Save attendance' }}
                            </x-filament::button>
                        </div>
                    @endunless
                </form>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
