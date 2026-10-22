<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case SuperAdmin = 'super_admin';
    case Principal = 'principal';
    case Staff = 'staff';
    case Teacher = 'teacher';
    case Student = 'student';
    case Parent = 'parent';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Principal => 'Principal',
            self::Staff => 'Staff',
            self::Teacher => 'Teacher',
            self::Student => 'Student',
            self::Parent => 'Parent',
        };
    }
}
