<?php

namespace App\Livewire;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Filament\Resources\ExaminationResults\ExaminationResultResource;
use App\Models\ExaminationResult;
use App\Models\Student;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class ExaminationResultsTableComponent extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    #[Url]
    public $admission_number = null;

    public bool $rateLimited = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ExaminationResult::query()
                    ->when(
                        filled($this->student()),
                        fn (Builder $query) => $query->whereBelongsTo($this->student()),
                        // Without a matched student, expose no results (blocks direct action calls)
                        fn (Builder $query) => $query->whereRaw('1 = 0')
                    )
            )
            ->paginated(false)
            ->columns(ExaminationResultResource::table($table)->getColumns())
            ->defaultSort('id', 'desc')
            ->filters([
                Filter::make('only_recent_result')
                    ->default(true)
                    ->label('Show only recent result')
                    ->query(fn (Builder $query): Builder => $query->limit(1)),
            ])
            ->recordActions([
                Action::make('download')
                    ->visible(fn (ExaminationResult $examinationResult) => $examinationResult->getFirstMedia('result_file'))
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(function (ExaminationResult $examinationResult) {
                        $media = $examinationResult->getFirstMedia('result_file');

                        return response()->download($media->getPath(), $media->file_name);
                    }),
            ])
            ->toolbarActions([]);
    }

    #[Computed]
    public function student(): ?Student
    {
        if (blank($this->admission_number)) {
            return null;
        }

        // Count only failed lookups so roll numbers can't be brute-forced: 15 misses per 10 minutes per IP
        $rateLimitKey = 'exam-result-lookup:'.request()->ip();

        $this->rateLimited = RateLimiter::tooManyAttempts($rateLimitKey, 15);

        if ($this->rateLimited) {
            return null;
        }

        $student = Student::where('admission_number', $this->admission_number)->first();

        if (! $student) {
            RateLimiter::hit($rateLimitKey, 600);
        }

        return $student;
    }

    public function render()
    {
        return view('livewire.examination-results-table-component');
    }
}
