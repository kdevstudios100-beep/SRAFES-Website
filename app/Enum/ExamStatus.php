<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Where an exam is in its life: marks are entered while Open, frozen once
 * Locked, and visible on the public results page once Published.
 * Worked out from the exam's timestamps, never stored.
 */
enum ExamStatus: string implements HasColor, HasLabel
{
    case Open = 'open';
    case Locked = 'locked';
    case Published = 'published';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Open => 'Open for marks',
            self::Locked => 'Locked',
            self::Published => 'Published',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Open => 'warning',
            self::Locked => 'gray',
            self::Published => 'success',
        };
    }
}
