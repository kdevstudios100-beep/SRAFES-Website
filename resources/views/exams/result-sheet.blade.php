<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Result sheet · {{ $exam->name }} · {{ $group->label() }}</title>
    @php
        $tally = $rows->countBy(fn ($row) => $row['result']?->value ?? 'incomplete');
        $marked = fn ($paper) => $rows->map(fn ($row) => $row['marks'][$paper->id])->filter();
    @endphp
    <style>
        :root { --ink: #111827; --muted: #6b7280; --line: #9ca3af; --fail: #b91c1c; --pass: #047857; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f4f6; color: var(--ink); font: 13px/1.35 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .toolbar { position: sticky; top: 0; z-index: 1; display: flex; gap: 12px; align-items: center; justify-content: space-between; padding: 12px 16px; background: #fff; border-bottom: 1px solid #d1d5db; }
        .toolbar button { padding: 8px 16px; border: 0; border-radius: 6px; background: #047857; color: #fff; font: inherit; font-weight: 600; cursor: pointer; }
        .page { max-width: 1200px; margin: 24px auto; padding: 24px; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; }
        .head { text-align: center; border-bottom: 2px solid var(--ink); padding-bottom: 10px; margin-bottom: 12px; }
        .head h1 { margin: 0; font-size: 20px; letter-spacing: .02em; }
        .head p { margin: 4px 0 0; color: var(--muted); }
        .scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; border: 1px solid var(--line); text-align: left; white-space: nowrap; }
        th { background: #f3f4f6; font-weight: 600; vertical-align: bottom; }
        th small { display: block; font-weight: 400; color: var(--muted); }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .name { white-space: normal; min-width: 140px; }
        .fail { color: var(--fail); font-weight: 600; }
        .result-pass { color: var(--pass); font-weight: 600; }
        .result-fail { color: var(--fail); font-weight: 600; }
        tfoot td { background: #f9fafb; font-weight: 600; }
        .summary { display: flex; flex-wrap: wrap; gap: 8px 24px; margin-top: 12px; }
        .legend { margin-top: 6px; color: var(--muted); font-size: 11px; }
        .signs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 48px; margin-top: 48px; text-align: center; color: var(--muted); }
        .signs div { border-top: 1px solid var(--ink); padding-top: 4px; }
        @page { size: A4 landscape; margin: 10mm; }
        @media (max-width: 600px) {
            .page { margin: 12px; padding: 12px; }
        }
        @media print {
            body { background: #fff; font-size: 11px; }
            .toolbar { display: none; }
            .page { max-width: none; margin: 0; padding: 0; border: 0; border-radius: 0; }
            .scroll { overflow: visible; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
            th { background: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span>Result sheet · {{ $group->label() }} · {{ $exam->fullName() }} · {{ $rows->count() }} {{ str('student')->plural($rows->count()) }}</span>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <main class="page">
        <div class="head">
            <h1>{{ config('app.name') }}</h1>
            <p>Result sheet · {{ $exam->name }} · {{ $group->label() }} · Session {{ $exam->academicSession->name }}@if ($exam->held_on) · {{ $exam->held_on->format('F Y') }}@endif</p>
        </div>

        <div class="scroll">
            <table>
                <thead>
                    <tr>
                        <th>Roll</th>
                        <th>Adm. no.</th>
                        <th class="name">Name</th>
                        @foreach ($papers as $paper)
                            <th class="num">{{ $paper->subject->name }}<small>{{ $paper->total_marks }} (pass {{ $paper->pass_marks }})</small></th>
                        @endforeach
                        <th class="num">Total<small>{{ $papers->sum('total_marks') }}</small></th>
                        <th class="num">%</th>
                        <th>Grade</th>
                        <th>Result</th>
                        <th class="num">Position</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row['enrollment']->roll_number ?? '-' }}</td>
                            <td>{{ $row['enrollment']->student->admission_number }}</td>
                            <td class="name">{{ $row['enrollment']->student->name }}</td>
                            @foreach ($papers as $paper)
                                @php($mark = $row['marks'][$paper->id])
                                <td @class(['num', 'fail' => $mark && ! $paper->passes($mark)])>
                                    {{ $mark === null ? '-' : ($mark->is_absent ? 'Abs' : $mark->obtained_marks) }}
                                </td>
                            @endforeach
                            <td class="num">{{ $row['obtained'] }}</td>
                            <td class="num">{{ number_format($row['percent'], 1) }}</td>
                            <td>{{ $row['grade'] ?? '-' }}</td>
                            <td @class(['result-pass' => $row['result']?->value === 'pass', 'result-fail' => $row['result']?->value === 'fail'])>
                                {{ $row['result']?->getLabel() ?? 'Incomplete' }}
                            </td>
                            <td class="num">{{ $row['position'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $papers->count() + 8 }}">No students are enrolled here.</td></tr>
                    @endforelse
                </tbody>
                @if ($rows->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="3">Passed in subject</td>
                            @foreach ($papers as $paper)
                                <td class="num">{{ $marked($paper)->filter(fn ($mark) => $paper->passes($mark))->count() }} / {{ $marked($paper)->count() }}</td>
                            @endforeach
                            <td colspan="5"></td>
                        </tr>
                        <tr>
                            <td colspan="3">Highest marks</td>
                            @foreach ($papers as $paper)
                                <td class="num">{{ $marked($paper)->max('obtained_marks') ?? '-' }}</td>
                            @endforeach
                            <td class="num">{{ $rows->whereNotNull('result')->max('obtained') ?? '-' }}</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="summary">
            <span>Students: <strong>{{ $rows->count() }}</strong></span>
            <span>Passed: <strong class="result-pass">{{ $tally['pass'] ?? 0 }}</strong></span>
            <span>Failed: <strong class="result-fail">{{ $tally['fail'] ?? 0 }}</strong></span>
            @if ($tally['incomplete'] ?? 0)
                <span>Marks missing: <strong>{{ $tally['incomplete'] }}</strong></span>
            @endif
            @php($decided = ($tally['pass'] ?? 0) + ($tally['fail'] ?? 0))
            @if ($decided)
                <span>Pass rate: <strong>{{ number_format(($tally['pass'] ?? 0) / $decided * 100, 1) }}%</strong></span>
            @endif
        </div>

        <p class="legend">Grades: {{ $legend }}. A student passes by passing every subject; Abs = absent (counts as a fail). Positions rank passing students by total marks. Printed {{ now()->format('j F Y') }}.</p>

        <div class="signs">
            <div>Class teacher</div>
            <div>Exam in-charge</div>
            <div>Principal</div>
        </div>
    </main>
</body>
</html>
