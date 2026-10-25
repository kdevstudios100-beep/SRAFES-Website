<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\AcademicSession;
use App\Support\Academics\StudentImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Two-step student import: check the file and show what would happen, then
 * import only the ready rows after the user confirms. Nothing is written
 * until the confirm step, and duplicates are always skipped.
 */
class ImportStudents extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|\UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Import students';

    protected string $view = 'filament.pages.import-students';

    public array $data = [];

    // Server-side only, so the browser can't point the import at another file
    #[Locked]
    public ?string $storedPath = null;

    #[Locked]
    public ?int $sessionId = null;

    #[Locked]
    public ?array $report = null;

    #[Locked]
    public bool $imported = false;

    public static function canAccess(): bool
    {
        return Gate::allows('import-students');
    }

    public function mount(): void
    {
        $this->form->fill(['academic_session_id' => AcademicSession::current()?->id]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(2)
            ->components([
                Select::make('academic_session_id')
                    ->label('Enroll into session')
                    ->options(fn () => AcademicSession::query()->latestFirst()->pluck('name', 'id'))
                    ->required()
                    ->helperText(fn () => AcademicSession::query()->exists() ? null : 'Create an academic session first.'),
                FileUpload::make('file')
                    ->label('CSV file')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                    ->maxSize(2048)
                    ->storeFiles(false)
                    ->required(),
            ]);
    }

    public function check(): void
    {
        abort_unless(static::canAccess(), 403);

        $state = $this->form->getState();
        $file = $state['file'];

        if (! $file instanceof TemporaryUploadedFile) {
            return;
        }

        $this->discardStoredFile();
        // Keep a private copy between the check and the import, outside the public upload area
        $this->storedPath = $file->storeAs('imports', Str::uuid().'.csv', 'local');
        $this->sessionId = (int) $state['academic_session_id'];
        $this->report = app(StudentImport::class)->analyze(Storage::disk('local')->path($this->storedPath), AcademicSession::find($this->sessionId))->toArray();
        $this->imported = false;

        if ($this->report['fatal']) {
            $this->discardStoredFile();
        }

        $this->form->fill(['academic_session_id' => $this->sessionId]);
    }

    public function import(): void
    {
        abort_unless(static::canAccess(), 403);

        if (! $this->storedPath || ! str_starts_with($this->storedPath, 'imports/') || ! Storage::disk('local')->exists($this->storedPath)) {
            Notification::make()->danger()->title('Check the file again before importing')->send();

            return;
        }

        $session = AcademicSession::findOrFail($this->sessionId);
        $report = app(StudentImport::class)->import(Storage::disk('local')->path($this->storedPath), $session);

        $this->report = $report->toArray();
        $this->imported = $report->fatal === null;
        $this->discardStoredFile();

        if ($this->imported) {
            Notification::make()->success()->title("{$report->imported} students imported into {$session->name}")->send();
        }
    }

    public function startOver(): void
    {
        $this->discardStoredFile();
        $this->report = null;
        $this->imported = false;
        $this->form->fill(['academic_session_id' => AcademicSession::current()?->id]);
    }

    public function getSessionName(): ?string
    {
        return $this->sessionId ? AcademicSession::find($this->sessionId)?->name : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('template')
                ->label('Download template')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->template()),
            Action::make('students')
                ->label('Back to students')
                ->color('gray')
                ->url(StudentResource::getUrl()),
        ];
    }

    private function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, StudentImport::COLUMNS, ',', '"', '');
            fputcsv($out, ['SR-1001', 'Ayesha', 'Khan', 'Baloch', 'Imran Khan', '2015-03-21', 'Class 3', 'A', '12', 'no'], ',', '"', '');
            fclose($out);
        }, 'student-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    private function discardStoredFile(): void
    {
        if ($this->storedPath && str_starts_with($this->storedPath, 'imports/')) {
            Storage::disk('local')->delete($this->storedPath);
        }

        $this->storedPath = null;
    }
}
