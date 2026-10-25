<?php

namespace App\Support\Academics;

/**
 * Outcome of checking (and possibly importing) a student CSV. A fatal error
 * means the whole file was rejected and no row was considered.
 */
class ImportReport
{
    public ?string $fatal = null;

    /** @var array<int, array{line: int, admission_number: ?string, name: string, placement: ?string, status: string, messages: array<string>}> */
    public array $rows = [];

    /** @var array<string> */
    public array $ignoredColumns = [];

    public int $imported = 0;

    public function fail(string $message): self
    {
        $this->fatal = $message;
        $this->rows = [];

        return $this;
    }

    public function ready(): array
    {
        return $this->withStatus(StudentImport::READY);
    }

    public function duplicates(): array
    {
        return $this->withStatus(StudentImport::DUPLICATE);
    }

    public function invalid(): array
    {
        return $this->withStatus(StudentImport::INVALID);
    }

    /**
     * Plain data for the Livewire page (models are dropped so nothing can be tampered with or re-hydrated).
     */
    public function toArray(): array
    {
        $strip = fn (array $rows) => array_values(array_map(
            fn (array $row) => array_intersect_key($row, array_flip(['line', 'admission_number', 'name', 'placement', 'status', 'messages'])),
            $rows,
        ));

        return [
            'fatal' => $this->fatal,
            'total' => count($this->rows),
            'ready' => $strip($this->ready()),
            'duplicates' => $strip($this->duplicates()),
            'invalid' => $strip($this->invalid()),
            'ignored_columns' => $this->ignoredColumns,
            'imported' => $this->imported,
        ];
    }

    private function withStatus(string $status): array
    {
        return array_values(array_filter($this->rows, fn (array $row) => $row['status'] === $status));
    }
}
