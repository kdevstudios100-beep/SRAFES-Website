<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Enum\AttendanceStatus;
use App\Support\Attendance\AttendanceSummary;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only attendance history for one student. Corrections are made on
 * the Take attendance page so the register rules apply.
 */
class AttendanceRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'attendanceRecords';

    protected static ?string $title = 'Attendance';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['enrollment.academicSession', 'enrollment.academicClass', 'enrollment.section', 'recordedBy']))
            ->description(fn () => app(AttendanceSummary::class)->forStudent($this->getOwnerRecord())
                ->map(fn (array $row) => "{$row['enrollment']->academicSession->name}, {$row['enrollment']->placement}: "
                    .($row['percent'] === null ? '-' : "{$row['percent']}%")
                    ." ({$row['counts']['present']} present, {$row['counts']['absent']} absent, {$row['counts']['late']} late, {$row['counts']['leave']} leave)")
                ->implode(' · ') ?: 'No attendance marked yet.')
            ->defaultSort('date', 'desc')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('date')->date('D, j M Y')->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('enrollment.placement')->label('Class / section'),
                TextColumn::make('remarks')->placeholder('-')->wrap(),
                TextColumn::make('recordedBy.name')->label('Marked by')->placeholder('-')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(AttendanceStatus::class),
            ]);
    }
}
