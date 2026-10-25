<x-filament-panels::page>
    <x-filament::section>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block">
                <span class="text-sm font-medium text-gray-950 dark:text-white">Exam</span>
                <x-filament::input.wrapper class="mt-1">
                    <x-filament::input.select wire:model.live="exam" :disabled="$this->exams->isEmpty()">
                        @forelse ($this->exams as $option)
                            <option value="{{ $option->id }}">{{ $option->fullName() }}</option>
                        @empty
                            <option value="">No exams yet</option>
                        @endforelse
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
        </div>

        @if ($this->exams->isEmpty())
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No exam has papers yet.</p>
        @elseif ($this->groups->isEmpty())
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Result sheets are shown to class teachers. You are not class teacher of a class in {{ $this->selectedExam->fullName() }}.</p>
        @endif
    </x-filament::section>

    @if ($this->register)
        @php
            $tally = $this->rows->countBy(fn ($row) => $row['result']?->value ?? 'incomplete');
        @endphp

        <x-filament::section>
            <x-slot name="heading">{{ $this->register->label() }} &middot; {{ $this->selectedExam->fullName() }}</x-slot>
            <x-slot name="description">
                {{ $this->selectedExam->status()->getLabel() }}.
                A student passes by passing every paper; absent counts as a fail. Positions rank passing students by total.
            </x-slot>

            <div class="mb-4 flex flex-wrap items-center gap-2">
                <x-filament::badge color="success">Passed: {{ $tally['pass'] ?? 0 }}</x-filament::badge>
                <x-filament::badge color="danger">Failed: {{ $tally['fail'] ?? 0 }}</x-filament::badge>
                @if ($tally['incomplete'] ?? 0)
                    <x-filament::badge color="gray">Marks missing: {{ $tally['incomplete'] }}</x-filament::badge>
                @endif
            </div>

            @if ($this->rows->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No students are enrolled here.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <tr>
                                <th class="py-2 pe-4 font-medium">Roll</th>
                                <th class="py-2 pe-4 font-medium">Student</th>
                                @foreach ($this->papers as $paper)
                                    <th class="py-2 pe-4 text-right font-medium">
                                        {{ $paper->subject->code ?: $paper->subject->name }}
                                        <div class="text-xs font-normal">/ {{ $paper->total_marks }}</div>
                                    </th>
                                @endforeach
                                <th class="py-2 pe-4 text-right font-medium">Total</th>
                                <th class="py-2 pe-4 text-right font-medium">%</th>
                                <th class="py-2 pe-4 font-medium">Grade</th>
                                <th class="py-2 pe-4 font-medium">Result</th>
                                <th class="py-2 text-right font-medium">Position</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($this->rows as $row)
                                @php($holder = app(\App\Support\Exams\ResultSheet::class)->isPositionHolder($row['position']))
                                <tr wire:key="result-{{ $row['enrollment']->id }}" @class(['bg-success-50 dark:bg-success-500/10' => $holder])>
                                    <td class="py-2 pe-4 text-gray-500 dark:text-gray-400">{{ $row['enrollment']->roll_number ?? '-' }}</td>
                                    <td class="py-2 pe-4">
                                        <div class="font-medium text-gray-950 dark:text-white">{{ $row['enrollment']->student->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $row['enrollment']->student->admission_number }}</div>
                                    </td>
                                    @foreach ($this->papers as $paper)
                                        @php($mark = $row['marks'][$paper->id])
                                        <td @class([
                                            'py-2 pe-4 text-right tabular-nums',
                                            'font-semibold text-danger-600 dark:text-danger-400' => $mark && ! $paper->passes($mark),
                                            'text-gray-400 dark:text-gray-500' => ! $mark,
                                        ])>
                                            {{ $mark === null ? '-' : ($mark->is_absent ? 'Abs' : $mark->obtained_marks) }}
                                        </td>
                                    @endforeach
                                    <td class="py-2 pe-4 text-right font-medium tabular-nums">{{ $row['obtained'] }} / {{ $row['total'] }}</td>
                                    <td class="py-2 pe-4 text-right tabular-nums">{{ number_format($row['percent'], 1) }}</td>
                                    <td class="py-2 pe-4 font-semibold">{{ $row['grade'] ?? '-' }}</td>
                                    <td class="py-2 pe-4">
                                        @if ($row['result'])
                                            <x-filament::badge :color="$row['result']->getColor()">{{ $row['result']->getLabel() }}</x-filament::badge>
                                        @else
                                            <x-filament::badge color="gray">Incomplete</x-filament::badge>
                                        @endif
                                    </td>
                                    <td class="py-2 text-right tabular-nums">
                                        @if ($holder)
                                            <x-filament::badge color="success" class="inline-flex">{{ app(\App\Support\Exams\ResultSheet::class)->ordinal($row['position']) }}</x-filament::badge>
                                        @else
                                            {{ $row['position'] ?? '-' }}
                                        @endif
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
