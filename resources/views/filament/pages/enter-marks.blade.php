<x-filament-panels::page>
    <x-filament::section>
        <div class="grid gap-4 sm:grid-cols-3">
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

            <label class="block">
                <span class="text-sm font-medium text-gray-950 dark:text-white">Subject</span>
                <x-filament::input.wrapper class="mt-1">
                    <x-filament::input.select wire:model.live="paper" :disabled="$this->papers->isEmpty()">
                        @forelse ($this->papers as $option)
                            <option value="{{ $option->id }}">{{ $option->subject->name }}</option>
                        @empty
                            <option value="">No subjects available</option>
                        @endforelse
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </label>
        </div>

        @if ($this->exams->isEmpty())
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No exam has papers yet. Exams and their papers are set up by the office under Exams.</p>
        @elseif ($this->groups->isEmpty())
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                You have no papers to mark in {{ $this->selectedExam->fullName() }}. Ask the office to assign you as class teacher, or as teacher of a subject, for {{ $this->selectedExam->academicSession->name }}.
            </p>
        @endif
    </x-filament::section>

    @if ($this->selectedPaper)
        @php
            $paperModel = $this->selectedPaper;
            $locked = $this->lockedReason;
            $entered = collect($marks)->filter(fn ($mark) => ($mark['absent'] ?? false) || filled($mark['obtained'] ?? null));
            $absent = collect($marks)->filter(fn ($mark) => $mark['absent'] ?? false)->count();
            $lastSaved = $this->existing->sortByDesc('updated_at')->first();
        @endphp

        <x-filament::section>
            <x-slot name="heading">
                {{ $paperModel->subject->name }} &middot; {{ $this->register->label() }} &middot; {{ $this->selectedExam->name }}
            </x-slot>
            <x-slot name="description">
                Out of {{ $paperModel->total_marks }}, pass mark {{ $paperModel->pass_marks }}.
                {{ $this->roster->count() }} {{ str('student')->plural($this->roster->count()) }}.
                @if ($lastSaved)
                    Last saved {{ $lastSaved->updated_at->diffForHumans() }}{{ $lastSaved->recordedBy ? ' by '.$lastSaved->recordedBy->name : '' }}.
                @endif
            </x-slot>

            @if ($locked)
                <div class="mb-4 rounded-lg bg-warning-50 p-3 text-sm text-warning-700 dark:bg-warning-400/10 dark:text-warning-400">
                    {{ $locked }}
                </div>
            @endif

            @if ($this->roster->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No students are enrolled here.</p>
            @else
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <x-filament::badge color="success">Entered: {{ $entered->count() - $absent }}</x-filament::badge>
                    <x-filament::badge color="info">Absent: {{ $absent }}</x-filament::badge>
                    @if ($this->roster->count() - $entered->count())
                        <x-filament::badge color="gray">Not entered: {{ $this->roster->count() - $entered->count() }}</x-filament::badge>
                    @endif
                </div>

                <form wire:submit="save">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-gray-200 text-gray-500 dark:border-white/10 dark:text-gray-400">
                                <tr>
                                    <th class="py-2 pe-3 font-medium">Roll</th>
                                    <th class="py-2 pe-3 font-medium">Student</th>
                                    <th class="py-2 pe-3 font-medium">Marks (of {{ $paperModel->total_marks }})</th>
                                    <th class="py-2 pe-3 font-medium">Absent</th>
                                    <th class="py-2 font-medium">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @foreach ($this->roster as $enrollment)
                                    @php
                                        $row = $marks[$enrollment->id] ?? [];
                                        $isAbsent = (bool) ($row['absent'] ?? false);
                                        $value = $row['obtained'] ?? null;
                                        $below = ! $isAbsent && filled($value) && ctype_digit((string) $value) && (int) $value < $paperModel->pass_marks;
                                    @endphp
                                    <tr wire:key="row-{{ $paperModel->id }}-{{ $enrollment->id }}">
                                        <td class="py-2 pe-3 align-middle text-gray-500 dark:text-gray-400">{{ $enrollment->roll_number ?? '-' }}</td>
                                        <td class="py-2 pe-3 align-middle">
                                            <div class="font-medium text-gray-950 dark:text-white">{{ $enrollment->student->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $enrollment->student->admission_number }}</div>
                                        </td>
                                        <td class="py-2 pe-3 align-middle">
                                            <div class="flex items-center gap-2">
                                                <x-filament::input.wrapper class="w-24" :disabled="$locked || $isAbsent" :valid="! $below">
                                                    <x-filament::input
                                                        type="number"
                                                        min="0"
                                                        max="{{ $paperModel->total_marks }}"
                                                        step="1"
                                                        inputmode="numeric"
                                                        aria-label="Marks for {{ $enrollment->student->name }}"
                                                        wire:model.blur="marks.{{ $enrollment->id }}.obtained"
                                                        :disabled="$locked || $isAbsent"
                                                    />
                                                </x-filament::input.wrapper>
                                                @if ($below)
                                                    <span class="text-xs font-medium text-danger-600 dark:text-danger-400">Below pass</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-2 pe-3 align-middle">
                                            <x-filament::input.checkbox
                                                aria-label="{{ $enrollment->student->name }} was absent"
                                                wire:model.live="marks.{{ $enrollment->id }}.absent"
                                                :disabled="(bool) $locked"
                                            />
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
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <x-filament::button type="submit" icon="heroicon-m-check-circle">
                                Save marks
                            </x-filament::button>
                            <span class="text-xs text-gray-500 dark:text-gray-400">You can save part-way and finish later. Leave a mark blank if it isn't ready yet.</span>
                        </div>
                    @endunless
                </form>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
