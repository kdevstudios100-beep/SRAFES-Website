<x-site-layout>
    <x-slot:hero class="bg-purple-25">
        <section class="relative w-full px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="mx-auto max-w-2xl lg:max-w-screen-xl">
                <div>
                    <h2 class="h2 text-purple-900 lg:text-center">
                        Career Opportunities
                    </h2>
                    <p
                        class="mx-auto mt-3 max-w-2xl text-xl leading-relaxed text-purple-800 lg:mt-4 lg:text-center"
                    >
                        Join our staff and make a difference in education
                    </p>
                </div>

                <div class="mt-12 sm:mt-16">
                    @if ($jobPosts->isEmpty())
                        <div class="mx-auto max-w-2xl text-center">
                            <p class="text-lg text-purple-700">
                                No active job postings at the moment. Please
                                check back later.
                            </p>
                        </div>
                    @else
                        <div class="mx-auto max-w-4xl space-y-4">
                            @foreach ($jobPosts as $jobPost)
                                <a
                                    href="{{ route('careers.show', $jobPost) }}"
                                    class="block rounded-lg border border-purple-200 bg-white p-6 shadow-sm transition hover:border-purple-400 hover:shadow-md"
                                >
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <div class="flex-1">
                                            <h3
                                                class="text-xl font-semibold text-purple-900"
                                            >
                                                {{ $jobPost->title }}
                                            </h3>
                                            @if ($jobPost->salary_range)
                                                <p
                                                    class="mt-1 text-sm text-purple-600"
                                                >
                                                    {{ $jobPost->salary_range }}
                                                </p>
                                            @endif
                                        </div>
                                        <div
                                            class="mt-3 sm:mt-0 sm:ml-6 sm:text-right"
                                        >
                                            @if ($jobPost->application_deadline)
                                                <p
                                                    class="text-sm font-medium text-purple-700"
                                                >
                                                    Apply by
                                                </p>
                                                <p
                                                    class="text-sm text-purple-600"
                                                >
                                                    {{ $jobPost->application_deadline->format('M d, Y') }}
                                                </p>
                                            @else
                                                <p
                                                    class="text-sm text-purple-600"
                                                >
                                                    No deadline
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </x-slot>

    <x-bottom-cta />
</x-site-layout>
