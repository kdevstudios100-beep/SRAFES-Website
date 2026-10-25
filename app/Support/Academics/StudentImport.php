<?php

namespace App\Support\Academics;

use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Student;
use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Reads a student CSV, checks every row, and imports only the rows that are
 * valid and not duplicates. analyze() never writes; import() re-checks the
 * file against the current database and writes all ready rows in one
 * transaction, so an import either fully succeeds or changes nothing.
 */
class StudentImport
{
    public const COLUMNS = [
        'admission_number', 'first_name', 'last_name', 'surname', 'father_name',
        'date_of_birth', 'class', 'section', 'roll_number', 'has_scholarship',
    ];

    public const REQUIRED_COLUMNS = ['admission_number', 'first_name', 'class'];

    // Other spellings people use for the same columns, after normalising (lowercase, underscores)
    private const ALIASES = [
        'admission_no' => 'admission_number', 'admission' => 'admission_number', 'adm_no' => 'admission_number', 'gr_number' => 'admission_number', 'gr_no' => 'admission_number',
        'name' => 'first_name', 'firstname' => 'first_name',
        'lastname' => 'last_name',
        'father' => 'father_name', 'fathers_name' => 'father_name', 'father_s_name' => 'father_name',
        'dob' => 'date_of_birth', 'birth_date' => 'date_of_birth',
        'class_name' => 'class', 'grade' => 'class',
        'section_name' => 'section',
        'roll_no' => 'roll_number', 'roll' => 'roll_number',
        'scholarship' => 'has_scholarship',
    ];

    private const DATE_FORMATS = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y'];

    public const READY = 'ready';

    public const DUPLICATE = 'duplicate';

    public const INVALID = 'invalid';

    public function __construct(private readonly Enrollments $enrollments) {}

    // $session: where the students will be enrolled, so roll numbers can be checked against it
    public function analyze(string $path, ?AcademicSession $session = null): ImportReport
    {
        return $this->check($path, $session);
    }

    public function import(string $path, AcademicSession $session): ImportReport
    {
        return DB::transaction(function () use ($path, $session) {
            // Check again: students may have been added since the preview
            $report = $this->check($path, $session);

            if ($report->fatal) {
                return $report;
            }

            $enrolledOn = Carbon::today()->between($session->starts_on, $session->ends_on) ? Carbon::today() : $session->starts_on;

            foreach ($report->ready() as $row) {
                $student = Student::create([
                    'admission_number' => $row['data']['admission_number'],
                    'first_name' => $row['data']['first_name'],
                    'last_name' => $row['data']['last_name'],
                    'surname' => $row['data']['surname'],
                    'father_name' => $row['data']['father_name'],
                    'date_of_birth' => $row['data']['date_of_birth'],
                    'has_scholarship' => $row['data']['has_scholarship'],
                    'is_active' => true,
                ]);

                $this->enrollments->enroll($student, $session, $row['class'], $row['section'], $row['data']['roll_number'], $enrolledOn);
            }

            $report->imported = count($report->ready());

            return $report;
        });
    }

    private function check(string $path, ?AcademicSession $session): ImportReport
    {
        $report = new ImportReport;
        $handle = @fopen($path, 'r');

        if ($handle === false) {
            return $report->fail('The file could not be read.');
        }

        try {
            $firstLine = (string) fgets($handle);
            $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
            // Excel saves CSV with semicolons in some regional settings
            $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
            $headers = array_map(fn ($header) => $this->normaliseHeader((string) $header), str_getcsv($firstLine, $delimiter, '"', ''));

            if ($missing = array_diff(self::REQUIRED_COLUMNS, $headers)) {
                return $report->fail('Missing required column(s): '.implode(', ', $missing).'. Download the template to see the expected columns.');
            }

            if (count(array_filter($headers)) !== count(array_unique(array_filter($headers)))) {
                return $report->fail('A column appears more than once in the header row.');
            }

            $report->ignoredColumns = array_values(array_filter($headers, fn ($header) => $header !== '' && ! in_array($header, self::COLUMNS, true)));

            $classes = AcademicClass::with('sections')->get();
            $existing = $this->existingStudents();
            $takenRolls = $session ? $this->takenRollNumbers($session) : [];
            $seenAdmission = [];
            $seenPerson = [];
            $seenRoll = [];
            $line = 1;
            $maxRows = (int) config('srafes.academics.import_max_rows', 2000);

            while (($cells = fgetcsv($handle, null, $delimiter, '"', '')) !== false) {
                $line++;

                if (count(array_filter($cells, fn ($cell) => trim((string) $cell) !== '')) === 0) {
                    continue;
                }

                if (count($report->rows) >= $maxRows) {
                    return $report->fail("The file has more than {$maxRows} students. Split it into smaller files.");
                }

                $values = [];
                foreach ($headers as $index => $header) {
                    if (in_array($header, self::COLUMNS, true)) {
                        $values[$header] = trim((string) ($cells[$index] ?? ''));
                    }
                }

                $report->rows[] = $this->checkRow($line, $values, $classes, $existing, $takenRolls, $seenAdmission, $seenPerson, $seenRoll);
            }
        } finally {
            fclose($handle);
        }

        if ($report->rows === []) {
            return $report->fail('The file has a header row but no students.');
        }

        return $report;
    }

    /**
     * @param  array<string, string>  $values
     * @param  array{admission: array<string, Student>, person: array<string, Student>}  $existing
     * @param  array<string, Enrollment>  $takenRolls  active placements in the target session, keyed by rollKey()
     */
    private function checkRow(int $line, array $values, Collection $classes, array $existing, array $takenRolls, array &$seenAdmission, array &$seenPerson, array &$seenRoll): array
    {
        $errors = [];
        $get = fn (string $column) => ($values[$column] ?? '') === '' ? null : $values[$column];

        foreach ($values as $column => $value) {
            if (mb_strlen($value) > 255) {
                $errors[] = "{$column} is longer than 255 characters.";
            }
        }

        $admission = $get('admission_number');
        $firstName = $get('first_name');

        if (! $admission) {
            $errors[] = 'Admission number is required.';
        }

        if (! $firstName) {
            $errors[] = 'First name is required.';
        }

        $dateOfBirth = null;
        if ($raw = $get('date_of_birth')) {
            $dateOfBirth = $this->parseDate($raw);

            if (! $dateOfBirth) {
                $errors[] = "Date of birth \"{$raw}\" is not a valid date (use YYYY-MM-DD or DD/MM/YYYY).";
            } elseif ($dateOfBirth->isFuture() || $dateOfBirth->year < 1950) {
                $errors[] = "Date of birth \"{$raw}\" is not plausible.";
                $dateOfBirth = null;
            }
        }

        $scholarship = $this->parseBoolean($get('has_scholarship'));
        if ($scholarship === null) {
            $errors[] = "Scholarship \"{$get('has_scholarship')}\" must be yes or no.";
        }

        $class = null;
        $section = null;
        if ($className = $get('class')) {
            $class = $this->findClass($classes, $className);

            if (! $class) {
                $errors[] = "Class \"{$className}\" does not exist. Add it under Classes first.";
            } elseif ($sectionName = $get('section')) {
                $section = $class->sections->first(fn ($section) => Str::lower($section->name) === Str::lower($sectionName));

                if (! $section) {
                    $errors[] = "Section \"{$sectionName}\" does not exist in {$class->name}. Add it to the class first.";
                }
            }
        } else {
            $errors[] = 'Class is required.';
        }

        $rollKey = $class && $get('roll_number') ? $this->rollKey($class->id, $section?->id, $get('roll_number')) : null;

        if ($rollKey && isset($takenRolls[$rollKey])) {
            $errors[] = "Roll number {$get('roll_number')} in {$takenRolls[$rollKey]->placement} already belongs to {$takenRolls[$rollKey]->student->name}.";
        } elseif ($rollKey && isset($seenRoll[$rollKey])) {
            $errors[] = "Roll number {$get('roll_number')} is also given on line {$seenRoll[$rollKey]} in the same class and section.";
        }

        $row = [
            'line' => $line,
            'admission_number' => $admission,
            'name' => trim(($firstName ?? '').' '.($get('last_name') ?? '')),
            'placement' => $class ? $class->name.($section ? ' - '.$section->name : '') : null,
            'status' => self::READY,
            'messages' => $errors,
            'class' => $class,
            'section' => $section,
            'data' => [
                'admission_number' => $admission,
                'first_name' => $firstName,
                'last_name' => $get('last_name'),
                'surname' => $get('surname'),
                'father_name' => $get('father_name'),
                'date_of_birth' => $dateOfBirth?->toDateString(),
                'roll_number' => $get('roll_number'),
                'has_scholarship' => (bool) $scholarship,
            ],
        ];

        if ($errors) {
            $row['status'] = self::INVALID;

            return $row;
        }

        $admissionKey = Str::lower($admission);
        $personKey = $this->personKey($firstName, $get('last_name'), $get('father_name'), $dateOfBirth?->toDateString());

        $duplicateOf = match (true) {
            isset($existing['admission'][$admissionKey]) => 'Admission number already belongs to '.$this->describe($existing['admission'][$admissionKey]).'.',
            isset($seenAdmission[$admissionKey]) => "Same admission number as line {$seenAdmission[$admissionKey]}.",
            $personKey && isset($existing['person'][$personKey]) => 'Same name, father\'s name and date of birth as '.$this->describe($existing['person'][$personKey]).'.',
            $personKey && isset($seenPerson[$personKey]) => "Same name, father's name and date of birth as line {$seenPerson[$personKey]}.",
            default => null,
        };

        $seenAdmission[$admissionKey] ??= $line;
        if ($personKey) {
            $seenPerson[$personKey] ??= $line;
        }

        if ($duplicateOf) {
            $row['status'] = self::DUPLICATE;
            $row['messages'] = [$duplicateOf];
        } elseif ($rollKey) {
            // Only students who will be imported hold a roll number for later lines
            $seenRoll[$rollKey] = $line;
        }

        return $row;
    }

    /**
     * @return array<string, Enrollment>
     */
    private function takenRollNumbers(AcademicSession $session): array
    {
        return Enrollment::query()
            ->active()
            ->where('academic_session_id', $session->id)
            ->whereNotNull('roll_number')
            ->with(['student', 'academicClass', 'section'])
            ->get()
            ->keyBy(fn (Enrollment $enrollment) => $this->rollKey($enrollment->academic_class_id, $enrollment->section_id, $enrollment->roll_number))
            ->all();
    }

    private function rollKey(int $classId, ?int $sectionId, string $rollNumber): string
    {
        return $classId.':'.($sectionId ?? 0).':'.trim($rollNumber);
    }

    /**
     * @return array{admission: array<string, Student>, person: array<string, Student>}
     */
    private function existingStudents(): array
    {
        $admission = [];
        $person = [];

        Student::query()
            ->select(['id', 'admission_number', 'first_name', 'last_name', 'father_name', 'date_of_birth'])
            ->each(function (Student $student) use (&$admission, &$person) {
                if (filled($student->admission_number)) {
                    $admission[Str::lower($student->admission_number)] = $student;
                }

                if ($key = $this->personKey($student->first_name, $student->last_name, $student->father_name, $student->date_of_birth ? Carbon::parse($student->date_of_birth)->toDateString() : null)) {
                    $person[$key] = $student;
                }
            });

        return ['admission' => $admission, 'person' => $person];
    }

    // Only a full match on name, father's name and birth date counts as the same person
    private function personKey(?string $firstName, ?string $lastName, ?string $fatherName, ?string $dateOfBirth): ?string
    {
        if (blank($firstName) || blank($fatherName) || blank($dateOfBirth)) {
            return null;
        }

        return Str::lower(preg_replace('/\s+/', ' ', "{$firstName}|{$lastName}|{$fatherName}|{$dateOfBirth}"));
    }

    private function describe(Student $student): string
    {
        return "{$student->name} (admission {$student->admission_number}, ID {$student->id})";
    }

    private function findClass(Collection $classes, string $name): ?AcademicClass
    {
        $normalised = Str::lower(preg_replace('/\s+/', ' ', $name));

        return $classes->first(fn (AcademicClass $class) => Str::lower(preg_replace('/\s+/', ' ', $class->name)) === $normalised)
            // "5" matches the class at position 5 on the ladder
            ?? (ctype_digit($name) ? $classes->first(fn (AcademicClass $class) => $class->sort_order === (int) $name) : null);
    }

    private function parseDate(string $value): ?Carbon
    {
        foreach (self::DATE_FORMATS as $format) {
            $date = DateTime::createFromFormat('!'.$format, $value);

            // Round-trip so overflowing dates like 31/02/2015 are rejected instead of rolled over
            if ($date && $date->format($format) === $value) {
                return Carbon::instance($date);
            }
        }

        return null;
    }

    private function parseBoolean(?string $value): ?bool
    {
        return match (Str::lower($value ?? '')) {
            '', 'no', 'n', 'false', '0' => false,
            'yes', 'y', 'true', '1' => true,
            default => null,
        };
    }

    private function normaliseHeader(string $header): string
    {
        $header = Str::lower(trim(preg_replace('/^\xEF\xBB\xBF/', '', $header)));
        $header = trim(preg_replace('/[^a-z0-9]+/', '_', $header), '_');

        return self::ALIASES[$header] ?? $header;
    }
}
