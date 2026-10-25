<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Result cards · {{ $exam->name }} · {{ $group->label() }}</title>
    <style>
        :root { --ink: #111827; --muted: #6b7280; --line: #d1d5db; --fail: #b91c1c; --pass: #047857; --holder: #166534; --holder-bg: #dcfce7; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f4f6; color: var(--ink); font: 14px/1.45 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .toolbar { position: sticky; top: 0; display: flex; gap: 12px; align-items: center; justify-content: space-between; padding: 12px 16px; background: #fff; border-bottom: 1px solid var(--line); }
        .toolbar button { padding: 8px 16px; border: 0; border-radius: 6px; background: #047857; color: #fff; font: inherit; font-weight: 600; cursor: pointer; }
        .card { max-width: 800px; margin: 24px auto; padding: 32px; background: #fff; border: 1px solid var(--line); border-radius: 8px; }
        .letterhead { display: flex; align-items: center; justify-content: center; gap: 16px; border-bottom: 3px double var(--holder); padding-bottom: 12px; margin-bottom: 16px; text-align: center; }
        .letterhead img { width: 84px; height: auto; flex: none; }
        .letterhead h1 { margin: 0; font-size: 19px; line-height: 1.25; color: var(--holder); text-transform: uppercase; letter-spacing: .03em; }
        .letterhead p { margin: 4px 0 0; color: var(--ink); font-weight: 600; }
        .ribbon { margin: -4px 0 16px; padding: 8px 12px; border: 1px solid var(--holder); border-left-width: 6px; border-radius: 6px; background: var(--holder-bg); color: var(--holder); font-weight: 700; text-align: center; text-transform: uppercase; letter-spacing: .05em; }
        .summary .holder { border-color: var(--holder); background: var(--holder-bg); }
        .summary .holder strong { color: var(--holder); }
        .details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px 24px; margin-bottom: 16px; }
        .details span { color: var(--muted); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border: 1px solid var(--line); text-align: left; }
        th { background: #f9fafb; font-weight: 600; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .fail { color: var(--fail); font-weight: 600; }
        tfoot td { font-weight: 700; }
        .summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; margin-top: 16px; }
        .summary div { padding: 8px; border: 1px solid var(--line); border-radius: 6px; text-align: center; }
        .summary span { display: block; color: var(--muted); font-size: 12px; }
        .summary strong { font-size: 18px; }
        .result-pass { color: var(--pass); }
        .result-fail { color: var(--fail); }
        .legend { margin-top: 12px; color: var(--muted); font-size: 12px; }
        .signs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 56px; text-align: center; color: var(--muted); }
        .signs div { border-top: 1px solid var(--ink); padding-top: 4px; }
        @media (max-width: 600px) {
            .card { margin: 12px; padding: 16px; }
            .details, .summary { grid-template-columns: 1fr 1fr; }
        }
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body { background: #fff; }
            .toolbar { display: none; }
            .card { margin: 0 auto; border: 0; border-radius: 0; page-break-after: always; break-after: page; }
            .card:last-child { page-break-after: auto; break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span>{{ $rows->count() }} {{ str('result card')->plural($rows->count()) }} · {{ $group->label() }} · {{ $exam->fullName() }}</span>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    @foreach ($rows as $row)
        @php($student = $row['enrollment']->student)
        <section class="card">
            @include('exams.partials.letterhead', ['title' => 'Result card · '.$exam->name.' · Session '.$exam->academicSession->name.($exam->held_on ? ' · '.$exam->held_on->format('F Y') : '')])

            @if ($sheet->isPositionHolder($row['position']))
                <div class="ribbon">{{ $sheet->ordinal($row['position']) }} position in {{ $group->label() }}</div>
            @endif

            <div class="details">
                <div><span>Name:</span> <strong>{{ $student->name }}</strong></div>
                <div><span>Admission no.:</span> {{ $student->admission_number }}</div>
                <div><span>Father's name:</span> {{ $student->father_name ?: '-' }}</div>
                <div><span>Class:</span> {{ $group->label() }} · <span>Roll no.:</span> {{ $row['enrollment']->roll_number ?? '-' }}</div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th class="num">Maximum</th>
                        <th class="num">Pass</th>
                        <th class="num">Obtained</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($papers as $paper)
                        @php($mark = $row['marks'][$paper->id])
                        <tr>
                            <td>{{ $paper->subject->name }}</td>
                            <td class="num">{{ $paper->total_marks }}</td>
                            <td class="num">{{ $paper->pass_marks }}</td>
                            <td @class(['num', 'fail' => $mark && ! $paper->passes($mark)])>
                                {{ $mark === null ? '-' : ($mark->is_absent ? 'Absent' : $mark->obtained_marks) }}
                            </td>
                            <td @class(['fail' => $mark && ! $paper->passes($mark)])>{{ $sheet->paperGrade($paper, $mark) ?? '-' }}</td>
                            <td>{{ $mark?->remarks }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="num">{{ $row['total'] }}</td>
                        <td></td>
                        <td class="num">{{ $row['obtained'] }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>

            <div class="summary">
                <div><span>Percentage</span><strong>{{ number_format($row['percent'], 2) }}%</strong></div>
                <div><span>Grade</span><strong>{{ $row['grade'] ?? '-' }}</strong></div>
                <div>
                    <span>Result</span>
                    <strong @class(['result-pass' => $row['result']?->value === 'pass', 'result-fail' => $row['result']?->value === 'fail'])>
                        {{ $row['result']?->getLabel() ?? 'Incomplete' }}
                    </strong>
                </div>
                <div @class(['holder' => $sheet->isPositionHolder($row['position'])])><span>Position</span><strong>{{ $row['position'] ? $sheet->ordinal($row['position']).' of '.$classSize : '-' }}</strong></div>
            </div>

            <p class="legend">Grades: {{ $legend }}. A student passes by passing every subject.</p>

            <div class="signs">
                <div>Class teacher</div>
                <div>Principal</div>
                <div>Parent / guardian</div>
            </div>
        </section>
    @endforeach
</body>
</html>
