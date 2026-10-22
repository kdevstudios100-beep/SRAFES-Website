@pushOnce('before.head.end')
    @filamentStyles
@endpushOnce

@pushOnce('before.body.end')
    @filamentScripts
@endpushOnce

<x-site-layout :loadJs="false">
    <x-slot:hero class="bg-purple-25">
        <section class="px-4 pt-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-screen-xl pb-20 pt-5 md:mt-10">
                <div class="relative">
                    <h2
                        class="h1 mx-auto max-w-3xl text-center text-purple-900"
                    >
                        {{ $jobPost->title }}
                    </h2>

                    <div
                        class="mx-auto mt-4 flex max-w-2xl flex-col items-center justify-center gap-3 sm:flex-row sm:gap-6"
                    >
                        @if ($jobPost->salary_range)
                            <p class="text-lg text-purple-700">
                                <span class="font-semibold">Salary:</span>
                                {{ $jobPost->salary_range }}
                            </p>
                        @endif

                        @if ($jobPost->application_deadline)
                            <p class="text-lg text-purple-700">
                                <span class="font-semibold">Deadline:</span>
                                {{ $jobPost->application_deadline->format('M d, Y') }}
                            </p>
                        @endif
                    </div>

                    <div
                        class="prose mx-auto mt-12 max-w-3xl prose-headings:text-purple-900 prose-a:text-purple-600 prose-strong:text-purple-900"
                    >
                        {!! $jobPost->content !!}
                    </div>

                    <div class="mx-auto mt-16 max-w-3xl">
                        @if ($jobPost->isOpen())
                            <livewire:job-application-form :job-post="$jobPost" />
                        @else
                            <div class="rounded-xl bg-white p-8 text-center shadow">
                                <p class="text-lg text-purple-700">
                                    This position is no longer accepting applications.
                                </p>
                            </div>
                        @endif
                    </div>

                    <div
                        class="mx-auto mt-12 flex max-w-3xl flex-col items-center justify-center overflow-hidden sm:flex-row"
                    >
                        <a
                            href="{{ route('careers') }}"
                            class="btn group bg-yellow-500 text-lg font-semibold text-purple-900 hover:bg-yellow-600"
                        >
                            Back to Careers
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </x-slot>

    <!-- Gradient transition -->
    <div
        class="h-32 w-full bg-gradient-to-b from-purple-25 to-white sm:h-40 lg:h-44"
    ></div>

    <x-bottom-cta class="-mt-40" />
</x-site-layout>
