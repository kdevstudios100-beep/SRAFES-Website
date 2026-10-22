<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ExaminationResultStatus: string implements HasColor, HasLabel
{
    case Pass = 'pass';
    case Promoted = 'promoted';
    case Fail = 'fail';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pass => 'Pass',
            self::Promoted => 'Promoted',
            self::Fail => 'Fail',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pass => 'success',
            self::Promoted => 'info',
            self::Fail => 'danger',
        };
    }
}
