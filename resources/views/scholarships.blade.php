@php
    use App\Models\Student;
@endphp

<x-site-layout>
    <x-slot:hero class="bg-purple-25">
        <section class="relative w-full px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="mx-auto max-w-2xl lg:max-w-screen-xl">
                <div>
                    <h2 class="h2 text-purple-900 lg:text-center">
                        {{ __('site/scholarships.heading') }}
                    </h2>
                    <p
                        class="mx-auto mt-3 max-w-2xl text-xl leading-relaxed text-purple-800 lg:mt-4 lg:text-center"
                    >
                        {{ __('site/scholarships.description') }}
                    </p>
                </div>

                <div
                    class="grid:3 mt-12 grid sm:mt-16 lg:grid-cols-4 lg:gap-6 xl:gap-8"
                >
                    @foreach (Student::with('academicClass')->hasScholarship()->get() as $student)
                        <x-profile-cards.simple
                            :profile-image="$student->getFirstMediaUrl('avatar')"
                            :name="$student->name"
                            :tag-line="$student->academicClass->name"
                        />
                    @endforeach
                </div>
            </div>
        </section>
    </x-slot>

    <x-bottom-cta />
</x-site-layout>
