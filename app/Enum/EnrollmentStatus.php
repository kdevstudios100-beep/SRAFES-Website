<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Only one enrollment per student is Active; every other status closes a
 * placement and records why the student moved on.
 */
enum EnrollmentStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Promoted = 'promoted';
    case Retained = 'retained';
    case Transferred = 'transferred';
    case Graduated = 'graduated';
    case Left = 'left';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Promoted => 'Promoted',
            self::Retained => 'Retained (repeating class)',
            self::Transferred => 'Transferred',
            self::Graduated => 'Graduated',
            self::Left => 'Left school',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Promoted, self::Graduated => 'info',
            self::Retained, self::Transferred => 'warning',
            self::Left => 'gray',
        };
    }
}
