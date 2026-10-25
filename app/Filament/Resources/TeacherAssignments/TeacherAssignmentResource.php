<?php

namespace App\Filament\Resources\TeacherAssignments;

use App\Enum\Permission;
use App\Filament\Resources\Students\Actions\PlacementActions;
use App\Filament\Resources\TeacherAssignments\Pages\CreateTeacherAssignment;
use App\Filament\Resources\TeacherAssignments\Pages\EditTeacherAssignment;
use App\Filament\Resources\TeacherAssignments\Pages\ListTeacherAssignments;
use App\Models\AcademicSession;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeacherAssignmentResource extends Resource
{
    protected static ?string $model = TeacherAssignment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'teacher assignment';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('academic_session_id')
                    ->label('Session')
                    ->options(fn () => AcademicSession::query()->latestFirst()->pluck('name', 'id'))
                    ->default(fn () => AcademicSession::current()?->id)
                    ->required(),
                Select::make('user_id')
                    ->label('Teacher')
                    // Anyone who can mark attendance can be given a class; teacher accounts are created under Users
                    ->options(fn () => User::query()->permission(Permission::MarkAttendance->value)->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->helperText('Create teacher accounts under Users with the Teacher role.')
                    // Checked on the always-filled teacher field: Laravel skips custom rules on empty
                    // fields, and an empty subject is exactly the class-teacher case
                    ->rule(fn (Get $get, ?TeacherAssignment $record) => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                        $subjectId = $get('subject_id');

                        $same = TeacherAssignment::query()
                            ->where('academic_session_id', $get('academic_session_id'))
                            ->where('academic_class_id', $get('academic_class_id'))
                            ->where(fn (Builder $query) => filled($get('section_id')) ? $query->where('section_id', $get('section_id')) : $query->whereNull('section_id'))
                            ->where(fn (Builder $query) => filled($subjectId) ? $query->where('subject_id', $subjectId) : $query->whereNull('subject_id'))
                            ->when($record, fn (Builder $query) => $query->whereKeyNot($record->getKey()))
                            ->with('user')
                            ->first();

                        if ($same) {
                            $fail(filled($subjectId)
                                ? "{$same->user->name} already teaches this subject here."
                                : "{$same->user->name} is already the class teacher here. Each class or section has one class teacher.");
                        }
                    }),
                ...PlacementActions::classAndSectionFields(),
                Select::make('subject_id')
                    ->label('Subject')
                    ->options(fn () => Subject::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                    ->placeholder('Class teacher (takes daily attendance)')
                    ->helperText('Leave empty to make this teacher the class teacher, who takes daily attendance. Pick a subject for a subject teacher.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'academicSession', 'academicClass', 'section', 'subject']))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('placement')
                    ->label('Class / section'),
                TextColumn::make('subject.name')
                    ->label('Role')
                    ->placeholder('Class teacher')
                    ->badge()
                    ->color(fn (TeacherAssignment $record) => $record->isClassTeacher() ? 'primary' : 'gray'),
                TextColumn::make('academicSession.name')
                    ->label('Session'),
            ])
            ->filters([
                SelectFilter::make('academic_session_id')
                    ->label('Session')
                    ->options(fn () => AcademicSession::query()->latestFirst()->pluck('name', 'id'))
                    ->default(fn () => AcademicSession::current()?->id),
                SelectFilter::make('user_id')
                    ->label('Teacher')
                    ->relationship('user', 'name')
                    ->searchable(),
                TernaryFilter::make('class_teacher')
                    ->label('Role')
                    ->trueLabel('Class teachers')
                    ->falseLabel('Subject teachers')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('subject_id'),
                        false: fn (Builder $query) => $query->whereNotNull('subject_id'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeacherAssignments::route('/'),
            'create' => CreateTeacherAssignment::route('/create'),
            'edit' => EditTeacherAssignment::route('/{record}/edit'),
        ];
    }
}
