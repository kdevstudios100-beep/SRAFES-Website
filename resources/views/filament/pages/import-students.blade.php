<x-filament-panels::page>
    @if (! $report || $report['fatal'])
        <x-filament::section>
            <x-slot name="heading">Upload a CSV file</x-slot>
            <x-slot name="description">
                Required columns: admission_number, first_name, class. Optional: last_name, surname, father_name,
                date_of_birth (YYYY-MM-DD or DD/MM/YYYY), section, roll_number, has_scholarship (yes/no).
                Classes and sections must already exist. Nothing is saved until you confirm.
            </x-slot>

            <form wire:submit="check" class="space-y-6">
                {{ $this->form }}

                <x-filament::button type="submit" icon="heroicon-m-magnifying-glass">
                    Check file
                </x-filament::button>
            </form>
        </x-filament::section>

        @if ($report && $report['fatal'])
            <x-filament::section icon="heroicon-o-x-circle" icon-color="danger">
                <x-slot name="heading">The file was not accepted</x-slot>
                <p class="text-sm">{{ $report['fatal'] }}</p>
            </x-filament::section>
        @endif
    @else
        @php
            $readyCount = count($report['ready']);
            $issues = array_merge($report['invalid'], $report['duplicates']);
            usort($issues, fn ($a, $b) => $a['line'] <=> $b['line']);
        @endphp

        <x-filament::section
            :icon="$imported ? 'heroicon-o-check-circle' : 'heroicon-o-clipboard-document-check'"
            :icon-color="$imported ? 'success' : 'primary'"
        >
            <x-slot name="heading">
                {{ $imported ? 'Import complete' : 'Check complete - nothing has been saved yet' }}
            </x-slot>
            <x-slot name="description">
                {{ $report['total'] }} {{ str('row')->plural($report['total']) }} in the file, session {{ $this->getSessionName() }}.
            </x-slot>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">{{ $imported ? 'Imported' : 'Ready to import' }}</dt>
                    <dd class="text-2xl font-semibold text-success-600 dark:text-success-400">{{ $imported ? $report['imported'] : $readyCount }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Duplicates (skipped)</dt>
                    <dd class="text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ count($report['duplicates']) }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Errors (skipped)</dt>
                    <dd class="text-2xl font-semibold text-danger-600 dark:text-danger-400">{{ count($report['invalid']) }}</dd>
                </div>
            </dl>

            @if ($report['ignored_columns'])
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    Ignored unknown columns: {{ implode(', ', $report['ignored_columns']) }}
                </p>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                @if (! $imported && $readyCount > 0)
                    <x-filament::button
                        wire:click="import"
                        wire:confirm="Import {{ $readyCount }} {{ str('student')->plural($readyCount) }} into {{ $this->getSessionName() }}?"
                        icon="heroicon-m-arrow-up-tray"
                    >
                        Import {{ $readyCount }} {{ str('student')->plural($readyCount) }}
                    </x-filament::button>
                @endif

                <x-filament::button wire:click="startOver" color="gray">
                    {{ $imported ? 'Import another file' : 'Choose a different file' }}
                </x-filament::button>
            </div>
        </x-filament::section>

        @if ($issues)
            <x-filament::section collapsible>
                <x-slot name="heading">Rows that {{ $imported ? 'were not' : 'will not be' }} imported ({{ count($issues) }})</x-slot>
                <x-slot name="description">Fix these rows and import them in a second file if needed.</x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <tr>
                                <th class="py-2 pe-4 font-medium">Line</th>
                                <th class="py-2 pe-4 font-medium">Admission no.</th>
                                <th class="py-2 pe-4 font-medium">Name</th>
                                <th class="py-2 pe-4 font-medium">Problem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($issues as $row)
                                <tr wire:key="issue-{{ $row['line'] }}">
                                    <td class="py-2 pe-4 align-top">{{ $row['line'] }}</td>
                                    <td class="py-2 pe-4 align-top">{{ $row['admission_number'] ?? '-' }}</td>
                                    <td class="py-2 pe-4 align-top">{{ $row['name'] ?: '-' }}</td>
                                    <td class="py-2 pe-4 align-top">
                                        <x-filament::badge :color="$row['status'] === 'duplicate' ? 'warning' : 'danger'" class="mb-1 inline-flex">
                                            {{ $row['status'] === 'duplicate' ? 'Duplicate' : 'Error' }}
                                        </x-filament::badge>
                                        @foreach ($row['messages'] as $message)
                                            <div>{{ $message }}</div>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif

        @if ($readyCount)
            <x-filament::section collapsible :collapsed="$readyCount > 20">
                <x-slot name="heading">Students {{ $imported ? 'imported' : 'ready to import' }} ({{ $readyCount }})</x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <tr>
                                <th class="py-2 pe-4 font-medium">Line</th>
                                <th class="py-2 pe-4 font-medium">Admission no.</th>
                                <th class="py-2 pe-4 font-medium">Name</th>
                                <th class="py-2 pe-4 font-medium">Class / section</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($report['ready'] as $row)
                                <tr wire:key="ready-{{ $row['line'] }}">
                                    <td class="py-2 pe-4">{{ $row['line'] }}</td>
                                    <td class="py-2 pe-4">{{ $row['admission_number'] }}</td>
                                    <td class="py-2 pe-4">{{ $row['name'] }}</td>
                                    <td class="py-2 pe-4">{{ $row['placement'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    @endif
</x-filament-panels::page>
