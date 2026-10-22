@php
    use App\Models\Employee;
    use App\Models\Student;
    use App\Models\AcademicClass;
@endphp

<section
    class="relative w-full bg-purple-600 px-4 py-16 sm:px-6 sm:py-24 lg:px-8"
>
    <!-- Container -->
    <div class="mx-auto max-w-screen-xl">
        <!-- Section header text -->
        <div>
            <h2 class="h2 mx-auto max-w-4xl text-center text-white">
                {{ __('site/about.stats.heading') }}
            </h2>
            <p
                class="mx-auto mt-4 max-w-3xl text-center text-xl leading-relaxed text-purple-50"
            >
                {{ __('site/about.stats.description') }}
            </p>
        </div>

        <!-- Stats -->
        <div
            class="mt-12 gap-y-16 sm:grid sm:grid-cols-3 sm:gap-x-6 sm:gap-y-12 md:gap-x-8 lg:gap-x-12"
        >
            <!-- Stat 1 -->
            <div class="text-center">
                <h4
                    class="font-written text-8xl leading-none text-white lg:text-9xl"
                >
                    {{ Employee::active()->count() }}
                </h4>
                <p
                    class="mt-0.5 text-lg font-medium text-purple-50 sm:mt-1.5 lg:text-xl"
                >
                    {{ __('site/about.stats.staff_label') }}
                </p>
            </div>

            <!-- Stat 2 -->
            <div class="mt-12 text-center sm:mt-0">
                <h4
                    class="font-written text-8xl leading-none text-white lg:text-9xl"
                >
                    {{ Student::count() }}
                </h4>
                <p
                    class="mt-0.5 text-lg font-medium text-purple-50 sm:mt-1.5 lg:text-xl"
                >
                    {{ __('site/about.stats.students_label') }}
                </p>
            </div>

            <!-- Stat 3 -->
            <div class="mt-12 text-center sm:mt-0">
                <h4
                    class="font-written text-8xl leading-none text-white lg:text-9xl"
                >
                    {{ AcademicClass::count() }}
                </h4>
                <p
                    class="mt-0.5 text-lg font-medium text-purple-50 sm:mt-1.5 lg:text-xl"
                >
                    {{ __('site/about.stats.classes_label') }}
                </p>
            </div>

            <!-- Stat 4 -->
            <div class="mt-12 text-center sm:mt-0">
                <h4
                    class="font-written text-8xl leading-none text-white lg:text-9xl"
                >
                    {{ Student::hasScholarship()->count() }}
                </h4>
                <p
                    class="mt-0.5 text-lg font-medium text-purple-50 sm:mt-1.5 lg:text-xl"
                >
                    {{ __('site/about.stats.students_on_scholarship_label') }}
                </p>
            </div>

            <!-- Stat 5 -->
            {{--
                <div class="mt-12 text-center sm:mt-0">
                <h4
                class="font-written text-8xl leading-none text-white lg:text-9xl"
                >
                4
                </h4>
                <p
                class="mt-0.5 text-lg font-medium text-purple-50 sm:mt-1.5 lg:text-xl"
                >
                Number of Sister Schools
                </p>
                </div>
            --}}

            <!-- Stat 6 -->
            {{--
                <div class="mt-12 text-center sm:mt-0">
                <h4
                class="font-written text-8xl leading-none text-white lg:text-9xl"
                >
                90%
                </h4>
                <p
                class="mt-0.5 text-lg font-medium text-purple-50 sm:mt-1.5 lg:text-xl"
                >
                Student state exam percentile
                </p>
                </div>
            --}}
        </div>
    </div>
</section>
