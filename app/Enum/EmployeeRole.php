<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum EmployeeRole: string implements HasLabel
{
    case Administrator = 'administrator';
    case SchoolChairman = 'school_chairman';
    case CoOrdinator = 'co_ordinator';
    case Principal = 'principal';
    case Teacher = 'teacher';
    case Employee = 'employee';
    case Clerk = 'clerk';
    case Peon = 'peon';
    case SchoolBoy = 'school_boy';
    case Driver = 'driver';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::SchoolChairman => 'School Chairman',
            self::CoOrdinator => 'Co-Ordinator',
            self::Principal => 'Principal',
            self::Teacher => 'Teacher',
            self::Employee => 'Employee',
            self::Clerk => 'Clerk',
            self::Peon => 'Peon',
            self::SchoolBoy => 'School Boy',
            self::Driver => 'Driver',
        };
    }
}
