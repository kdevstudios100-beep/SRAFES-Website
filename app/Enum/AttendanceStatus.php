<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AttendanceStatus: string implements HasColor, HasLabel
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    // Excused absence, e.g. sick or family leave approved by the school
    case Leave = 'leave';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::Late => 'Late',
            self::Leave => 'Leave',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Present => 'success',
            self::Absent => 'danger',
            self::Late => 'warning',
            self::Leave => 'info',
        };
    }

    // One-letter code for compact registers
    public function short(): string
    {
        return match ($this) {
            self::Present => 'P',
            self::Absent => 'A',
            self::Late => 'L',
            self::Leave => 'LV',
        };
    }
}
