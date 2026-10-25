<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Enum\EnrollmentStatus;
use App\Models\Enrollment;
use App\Support\Academics\Enrollments;
use Closure;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The student's placement history. Placements change only through the
 * placement actions; here staff can correct a roll number or add notes.
 */
class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Enrollment history';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('roll_number')
                    ->maxLength(50)
                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? trim($state) : null)
                    // Only placements still in force compete for a roll number; history may repeat one
                    ->rule(fn (?Enrollment $record) => function (string $attribute, $value, Closure $fail) use ($record) {
                        if (! $record || $record->status !== EnrollmentStatus::Active) {
                            return;
                        }

                        $holder = app(Enrollments::class)->rollNumberHolder($record->academicSession, $record->academicClass, $record->section, $value, except: $record);

                        if ($holder) {
                            $fail("Roll number {$holder->roll_number} in {$holder->placement} already belongs to {$holder->student->name}.");
                        }
                    }),
                Textarea::make('notes')->rows(3)->maxLength(1000)->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['academicSession', 'academicClass', 'section']))
            // Newest placement first; id breaks ties between placements in the same session
            ->defaultSort(fn (Builder $query) => $query->orderByDesc('id'))
            ->paginated(false)
            ->columns([
                TextColumn::make('academicSession.name')->label('Session'),
                TextColumn::make('placement')->label('Class / section'),
                TextColumn::make('roll_number')->label('Roll no.')->placeholder('-'),
                TextColumn::make('status')->badge(),
                TextColumn::make('enrolled_on')->date()->placeholder('-'),
                TextColumn::make('ended_on')->date()->placeholder('-'),
                TextColumn::make('notes')->limit(60)->tooltip(fn ($record) => $record->notes)->placeholder('-')->wrap(),
            ])
            ->recordActions([
                EditAction::make()->label('Edit roll no. / notes'),
            ]);
    }
}
