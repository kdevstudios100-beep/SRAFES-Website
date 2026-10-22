<div>
    <div class="relative z-10 mx-auto w-full rounded-xl bg-white p-8 shadow">
        <h3 class="mb-6 text-2xl font-bold text-purple-900">
            Apply for this Position
        </h3>

        @if($submitted)
            <div class="rounded-lg bg-green-50 p-6 text-center">
                <x-heroicon-o-check-circle class="mx-auto h-16 w-16 text-green-600" />
                <h4 class="mt-4 text-xl font-semibold text-green-900">
                    Application Submitted Successfully!
                </h4>
                <p class="mt-2 text-green-800">
                    Thank you for applying. We will review your application and get back to you soon.
                </p>
            </div>
        @else
            <form wire:submit="submit">
                {{ $this->form }}

                <div class="mt-6">
                    <button
                        type="submit"
                        class="btn group bg-purple-600 text-lg font-semibold text-white hover:bg-purple-700"
                    >
                        Submit Application
                    </button>
                </div>
            </form>
        @endif
    </div>

    <x-filament-actions::modals />
</div>
