<?php

namespace App\Livewire;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Schema;
use App\Models\JobApplication;
use App\Models\JobPost;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class JobApplicationForm extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $data = [];

    public JobPost $jobPost;

    public bool $submitted = false;

    public function mount(JobPost $jobPost): void
    {
        $this->jobPost = $jobPost;
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(JobApplication::class)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                SpatieMediaLibraryFileUpload::make('resume')
                    ->label('Resume')
                    ->required()
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->collection('resume')
                    ->disk('local')
                    ->maxSize(5120),
                SpatieMediaLibraryFileUpload::make('cover_letter')
                    ->label('Cover Letter')
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->collection('cover_letter')
                    ->disk('local')
                    ->maxSize(5120),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        abort_unless($this->jobPost->isOpen(), 403);

        // Limit each IP to 3 applications per hour to stop spam uploads
        $rateLimitKey = 'job-application:'.request()->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $minutes = ceil(RateLimiter::availableIn($rateLimitKey) / 60);

            throw ValidationException::withMessages([
                'data.name' => "Too many applications submitted. Please try again in {$minutes} minutes.",
            ]);
        }

        $data = $this->form->getState();

        RateLimiter::hit($rateLimitKey, 3600);

        $jobApplication = JobApplication::create([
            'job_post_id' => $this->jobPost->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        $this->form->model($jobApplication)->saveRelationships();

        $this->form->fill();

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.job-application-form');
    }
}
