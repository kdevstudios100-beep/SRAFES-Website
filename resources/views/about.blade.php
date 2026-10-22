@php
    use App\Models\Employee;
    use App\Models\Student;
    use App\Models\AcademicClass;
@endphp

<x-site-layout>
    <x-slot:hero class="bg-purple-25">
        <section class="px-4 pt-16 sm:px-6 lg:px-8">
            <!-- Container -->
            <div class="mx-auto max-w-screen-xl">
                <!-- Page header -->
                <div class="relative">
                    <h2
                        class="h1 mx-auto max-w-3xl text-center text-purple-900"
                    >
                        {{ __('site/about.heading') }}
                    </h2>
                    <p
                        class="mx-auto mt-4 max-w-2xl text-center text-xl leading-relaxed text-purple-800 sm:mt-5"
                    >
                        {{ __('site/about.description') }}
                    </p>
                </div>

                <!-- School images grid -->
                <div
                    class="mt-14 grid grid-flow-row-dense grid-cols-2 gap-2 sm:mt-16 sm:grid-cols-3 sm:gap-3 md:gap-4 lg:mt-24 lg:gap-6"
                >
                    <x-img
                        class="h-30vw w-full rounded-2xl object-cover lg:h-80"
                        alt="School grid 01"
                        src="/images/school-grid-01.jpg"
                    />

                    <x-img
                        class="col-span-2 h-30vw w-full rounded-2xl object-cover lg:h-80"
                        src="/images/school-grid-02.jpg"
                        alt="School grid 02"
                    />

                    <x-img
                        class="h-30vw w-full rounded-2xl object-cover lg:h-80"
                        src="/images/school-grid-03.jpg"
                        alt="School grid 03"
                    />

                    <x-img
                        class="h-30vw w-full rounded-2xl object-cover lg:h-80"
                        src="/images/school-grid-04.jpg"
                        alt="School grid 04"
                    />

                    <x-img
                        class="h-30vw w-full rounded-2xl object-cover lg:h-80"
                        src="/images/school-grid-05.jpg"
                        alt="School grid 05"
                    />
                </div>

                <!-- About school -->
                <div
                    class="prose prose-lg mx-auto mt-14 sm:prose-xl sm:mt-16 lg:mt-24"
                >
                    {{ __('site/about.content') }}
                </div>
            </div>
        </section>
    </x-slot>

    <!-- Gradient transition -->
    <div
        class="h-32 w-full bg-gradient-to-b from-purple-25 to-white sm:h-40 lg:h-44"
    ></div>

    <!-- About blocks -->
    <section
        class="overflow-hidden bg-white px-4 pb-28 sm:px-6 sm:pb-36 lg:px-8"
    >
        <!-- Container -->
        <div class="mx-auto max-w-screen-xl">
            <!-- Section header -->
            <div class="px-4 text-center sm:px-6 lg:px-8">
                <h3 class="h2 text-purple-900">
                    @php
                        $heading = str(__('site/about.blocks.heading'))->explode("\n");
                    @endphp

                    <span class="block">
                        {{ Arr::get($heading, 0) }}
                    </span>

                    <!-- Underlined text -->
                    <span class="relative block">
                        <span class="relative">
                            <img
                                class="absolute inset-0 translate-y-9 transform sm:translate-y-10 xl:translate-y-12"
                                src="/images/underline-simple-light-purple.svg"
                            />
                            <span class="relative">
                                {{ Arr::get($heading, 1) }}
                            </span>
                        </span>
                    </span>
                </h3>
            </div>

            <!-- Block 1 -->
            <div
                class="mx-auto mt-20 grid max-w-xl gap-14 sm:mt-24 sm:gap-16 lg:mt-44 lg:max-w-none lg:grid-cols-12 lg:gap-8"
            >
                <!-- Block text content -->
                <div
                    class="relative z-10 order-2 flex flex-col justify-center lg:order-1 lg:col-span-6 lg:text-left"
                >
                    <div>
                        <span
                            class="inline-block -rotate-1 rounded-full bg-purple-200 px-4 py-2 font-medium text-purple-700 shadow-md"
                        >
                            {{ __('site/about.blocks.first_block.tagline') }}
                        </span>
                    </div>

                    <div>
                        <h1 class="h3 mt-3.5 font-bold text-purple-900">
                            {{ __('site/about.blocks.first_block.heading') }}
                        </h1>
                        <p
                            class="mt-3 max-w-xl text-lg text-purple-800 sm:text-xl sm:leading-relaxed"
                        >
                            {{ __('site/about.blocks.first_block.description') }}
                        </p>
                    </div>
                </div>

                <!-- Block graphics -->
                <div
                    class="relative order-1 mx-auto w-full max-w-xl lg:order-2 lg:col-span-6 lg:mx-0 lg:flex lg:max-w-none lg:items-center"
                >
                    <!-- Blob background decoration on large screens -->
                    <div class="hidden lg:block">
                        <img
                            src="/images/blob-light-yellow.svg"
                            class="absolute inset-0 h-full w-full transform lg:scale-135"
                            alt=""
                        />
                    </div>

                    <!-- Grid background decoration on small screens -->
                    <img
                        src="/images/dots-large-strip.svg"
                        class="absolute left-1/2 top-0 origin-top -translate-x-1/2 -translate-y-8 scale-80 transform sm:scale-100 lg:hidden"
                        alt=""
                    />

                    <div
                        class="relative mx-auto w-full rounded-3xl shadow-lg lg:ml-auto lg:mr-0 lg:max-w-lg"
                    >
                        <div class="relative block w-full">
                            <!-- Corner dots decoration -->
                            <img
                                class="absolute -left-20 -top-20 z-10 hidden w-40 transform lg:block xl:-top-20 xl:w-48"
                                src="/images/dots.svg"
                                alt=""
                            />

                            <!-- Block image -->
                            <figure class="aspect-h-10 aspect-w-12 md:order-1">
                                <x-img
                                    src="/images/about-01.jpg"
                                    :relative="false"
                                    class="h-full w-full rounded-3xl object-cover object-center shadow-xl"
                                    alt="About 01"
                                />
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Block 2 -->
            <div
                class="mx-auto mt-20 grid max-w-xl gap-14 sm:mt-24 sm:gap-16 lg:mt-44 lg:max-w-none lg:grid-cols-12 lg:gap-8"
            >
                <!-- Block text content -->
                <div
                    class="relative z-10 order-2 flex flex-col justify-center lg:col-span-6 lg:text-left"
                >
                    <div>
                        <span
                            class="inline-block -rotate-1 rounded-full bg-purple-200 px-4 py-2 font-medium text-purple-700 shadow-md"
                        >
                            {{ __('site/about.blocks.second_block.tagline') }}
                        </span>
                    </div>

                    <div>
                        <h1 class="h3 mt-3.5 font-bold text-purple-900">
                            {{ __('site/about.blocks.second_block.heading') }}
                        </h1>
                        <p
                            class="mt-3 max-w-xl text-lg text-purple-800 sm:text-xl sm:leading-relaxed"
                        >
                            {{ __('site/about.blocks.second_block.description') }}
                        </p>
                    </div>
                </div>

                <!-- Block graphics -->
                <div
                    class="relative order-1 mx-auto w-full max-w-xl lg:col-span-6 lg:mx-0 lg:flex lg:max-w-none lg:items-center"
                >
                    <!-- Blob background decoration on large screens -->
                    <div class="hidden lg:block">
                        <img
                            src="/images/blob-light-purple.svg"
                            class="absolute inset-0 h-full w-full transform lg:scale-135"
                            alt=""
                        />
                    </div>

                    <!-- Grid background decoration on small screens -->
                    <img
                        src="/images/dots-large-strip.svg"
                        class="absolute left-1/2 top-0 origin-top -translate-x-1/2 -translate-y-8 scale-80 transform sm:scale-100 lg:hidden"
                        alt=""
                    />

                    <div
                        class="relative mx-auto w-full rounded-3xl shadow-lg lg:mx-0 lg:max-w-lg"
                    >
                        <div class="relative block w-full">
                            <!-- Corner dots decoration -->
                            <img
                                class="absolute -right-20 -top-20 z-10 hidden w-40 transform lg:block xl:-top-20 xl:w-48"
                                src="/images/dots.svg"
                                alt=""
                            />

                            <!-- Block image -->
                            <figure class="aspect-h-10 aspect-w-12 md:order-1">
                                <x-img
                                    src="/images/about-02.jpg"
                                    :relative="false"
                                    class="h-full w-full rounded-3xl object-cover object-center shadow-xl"
                                    alt="About 02"
                                />
                            </figure>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Block 3 -->
            <div
                class="mx-auto mt-20 grid max-w-xl gap-14 sm:mt-24 sm:gap-16 lg:mt-44 lg:max-w-none lg:grid-cols-12 lg:gap-8"
            >
                <!-- Block text content -->
                <div
                    class="relative z-10 order-2 flex flex-col justify-center lg:order-1 lg:col-span-6 lg:text-left"
                >
                    <div>
                        <span
                            class="inline-block -rotate-1 rounded-full bg-purple-200 px-4 py-2 font-medium text-purple-700 shadow-md"
                        >
                            {{ __('site/about.blocks.third_block.tagline') }}
                        </span>
                    </div>

                    <div>
                        <h1 class="h3 mt-3.5 font-bold text-purple-900">
                            {{ __('site/about.blocks.third_block.heading') }}
                        </h1>
                        <p
                            class="mt-3 max-w-xl text-lg text-purple-800 sm:text-xl sm:leading-relaxed"
                        >
                            {{ __('site/about.blocks.third_block.description') }}
                        </p>
                    </div>
                </div>

                <!-- Block graphics -->
                <div
                    class="relative order-1 mx-auto w-full max-w-xl lg:order-2 lg:col-span-6 lg:mx-0 lg:flex lg:max-w-none lg:items-center"
                >
                    <!-- Blob background decoration on large screens -->
                    <div class="hidden lg:block">
                        <img
                            src="/images/blob-light-rose.svg"
                            class="absolute inset-0 h-full w-full transform lg:scale-135"
                            alt=""
                        />
                    </div>

                    <!-- Grid background decoration on small screens -->
                    <img
                        src="/images/dots-large-strip.svg"
                        class="absolute left-1/2 top-0 origin-top -translate-x-1/2 -translate-y-8 scale-80 transform sm:scale-100 lg:hidden"
                        alt=""
                    />

                    <div
                        class="relative mx-auto w-full rounded-3xl shadow-lg lg:ml-auto lg:mr-0 lg:max-w-lg"
                    >
                        <div class="relative block w-full">
                            <!-- Corner dots decoration -->
                            <img
                                class="absolute -left-20 -top-20 z-10 hidden w-40 transform lg:block xl:-top-20 xl:w-48"
                                src="/images/dots.svg"
                                alt=""
                            />

                            <!-- Block image-->
                            <figure class="aspect-h-10 aspect-w-12 md:order-1">
                                <x-img
                                    src="/images/about-03.jpg"
                                    :relative="false"
                                    class="h-full w-full rounded-3xl object-cover object-center shadow-xl"
                                    alt="About 03"
                                />
                            </figure>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-stats />

    <!-- Section separator-->
    <div class="bg-purple-600 px-4 sm:px-6 lg:px-8">
        <hr class="mx-auto max-w-screen-xl border-purple-500" />
    </div>

    <!-- Team section -->
    <section id="team">
        <!-- Top purple background section -->
        <div class="bg-purple-600 px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <!-- Section header text -->
            <div class="mx-auto max-w-2xl lg:max-w-screen-xl">
                <div class="lg:grid lg:grid-cols-2 lg:gap-16">
                    <div class="flex items-center">
                        <h3
                            class="h2 max-w-4xl text-white sm:text-center lg:text-left"
                        >
                            {{ __('site/about.staff.heading') }}
                        </h3>
                    </div>
                    <div class="flex items-center">
                        <p
                            class="mt-5 text-xl leading-relaxed text-purple-50 sm:text-center lg:mt-0 lg:text-left"
                        >
                            {{ __('site/about.staff.description') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- background to create overlay effect -->
        <div class="h-32 w-full bg-purple-600"></div>

        <!-- Team section -->
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl -translate-y-32 lg:max-w-screen-xl">
                <div
                    class="grid gap-y-16 sm:grid-cols-2 sm:gap-x-6 lg:grid-cols-3 lg:gap-x-8"
                >
                    @foreach (Employee::active()->get() as $employee)
                        <div>
                            <!-- Staff member image -->
                            <div class="aspect-h-2 aspect-w-3">
                                <img
                                    src="{{ $employee->getFirstMediaUrl('avatar') }}"
                                    class="rounded-2xl object-cover"
                                    alt="{{ $employee->name }}'s profile image"
                                />
                            </div>

                            <!-- Staff member info -->
                            <div class="flex items-center justify-between">
                                <div class="mt-3 text-xl font-medium">
                                    <p
                                        class="font-semibold tracking-wide text-purple-900"
                                    >
                                        {{ $employee->name }}
                                    </p>
                                    <p class="text-lg text-purple-600">
                                        {{ $employee->role->getLabel() }}
                                    </p>
                                </div>

                                <!-- Social links -->
                                <div class="flex space-x-1.5">
                                    @if ($employee->x_url)
                                        <!-- Twitter -->
                                        <a
                                            href="{{ $employee->x_url }}"
                                            class="durarion-300 text-purple-600 transition ease-in-out hover:scale-125 hover:text-purple-500"
                                        >
                                            <!-- TablerIcon name: brand-twitter -->
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-6 w-6"
                                                width="44"
                                                height="44"
                                                viewBox="0 0 24 24"
                                                stroke-width="1.5"
                                                stroke="currentColor"
                                                fill="none"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path
                                                    stroke="none"
                                                    d="M0 0h24v24H0z"
                                                    fill="none"
                                                />
                                                <path
                                                    d="M22 4.01c-1 .49 -1.98 .689 -3 .99c-1.121 -1.265 -2.783 -1.335 -4.38 -.737s-2.643 2.06 -2.62 3.737v1c-3.245 .083 -6.135 -1.395 -8 -4c0 0 -4.182 7.433 4 11c-1.872 1.247 -3.739 2.088 -6 2c3.308 1.803 6.913 2.423 10.034 1.517c3.58 -1.04 6.522 -3.723 7.651 -7.742a13.84 13.84 0 0 0 .497 -3.753c-.002 -.249 1.51 -2.772 1.818 -4.013z"
                                                />
                                            </svg>
                                        </a>
                                    @endif

                                    @if ($employee->facebook_url)
                                        <!-- Facebook -->
                                        <a
                                            href="{{ $employee->facebook_url }}"
                                            class="durarion-300 text-purple-600 transition ease-in-out hover:scale-125 hover:text-purple-500"
                                        >
                                            <!-- TablerIcon name: brand-facebook -->
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-6 w-6"
                                                width="44"
                                                height="44"
                                                viewBox="0 0 24 24"
                                                stroke-width="1.5"
                                                stroke="currentColor"
                                                fill="none"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path
                                                    stroke="none"
                                                    d="M0 0h24v24H0z"
                                                    fill="none"
                                                />
                                                <path
                                                    d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3"
                                                />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- School values -->
    {{--
        <section class="-mt-8 bg-white px-4 sm:mt-0 sm:px-6 sm:py-4 lg:px-8">
        <!-- Container -->
        <div class="mx-auto max-w-3xl lg:max-w-screen-xl">
        <div class="lg:grid lg:grid-cols-2 lg:gap-10 xl:gap-16">
        <!-- Section content -->
        <div class="flex flex-col justify-center">
        <h2 class="h2 text-purple-900 sm:text-center lg:text-left">
        Our core values and principles
        </h2>
        <p
        class="mx-auto mt-4 max-w-xl text-lg leading-relaxed text-purple-800 sm:text-center md:mt-5 lg:mx-0 lg:text-left"
        >
        Pellentesque in ipsum id orci porta dapibus. Nulla quis
        lorem ut libero malesuada feugiat. Donec rutrum congue
        leo eget malesuada. Curabitur non nulla sit amet nisl
        tempus convallis quis ac lectus.
        </p>
        
        <!-- Values -->
        <div
        class="mt-10 grid max-w-4xl gap-6 sm:mx-auto sm:grid-cols-2 lg:mx-0 lg:max-w-md lg:grid-cols-1"
        >
        <!-- Value 1 -->
        <div class="flex">
        <div class="w-14">
        <img
        src="/images/checkmark.svg"
        class="h-7 w-7"
        alt=""
        />
        </div>
        <div class="w-full">
        <h5
        class="flex items-center text-xl font-semibold text-purple-900"
        >
        Excellence and Innovation
        </h5>
        <p class="mt-1 text-base text-purple-800">
        Lorem ipsum dolor sit amet, consectetur
        adipisicing elit, do eius mod tempor
        incididunt ut labore et
        </p>
        </div>
        </div>
        
        <!-- Value 2 -->
        <div class="flex">
        <div class="w-14">
        <img
        src="/images/checkmark.svg"
        class="h-7 w-7"
        alt=""
        />
        </div>
        <div class="w-full">
        <h5
        class="flex items-center text-xl font-semibold text-purple-900"
        >
        Creativity
        </h5>
        <p class="mt-1 text-base text-purple-800">
        Lorem ipsum dolor sit amet, consectetur
        adipisicing elit, do eius mod tempor
        incididunt ut labore et
        </p>
        </div>
        </div>
        
        <!-- Value 3 -->
        <div class="flex">
        <div class="w-14">
        <img
        src="/images/checkmark.svg"
        class="h-7 w-7"
        alt=""
        />
        </div>
        <div class="w-full">
        <h5
        class="flex items-center text-xl font-semibold text-purple-900"
        >
        Trustworthiness
        </h5>
        <p class="mt-1 text-base text-purple-800">
        Lorem ipsum dolor sit amet, consectetur
        adipisicing elit, do eius mod tempor
        incididunt ut labore et
        </p>
        </div>
        </div>
        
        <!-- Value 4 -->
        <div class="flex">
        <div class="w-14">
        <img
        src="/images/checkmark.svg"
        class="h-7 w-7"
        alt=""
        />
        </div>
        <div class="w-full">
        <h5
        class="flex items-center text-xl font-semibold text-purple-900"
        >
        Nurturing Individuality
        </h5>
        <p class="mt-1 text-base text-purple-800">
        Lorem ipsum dolor sit amet, consectetur
        adipisicing elit, do eius mod tempor
        incididunt ut labore et
        </p>
        </div>
        </div>
        </div>
        </div>
        
        <!-- Section image -->
        <div
        class="mx-auto mt-16 w-full max-w-xl lg:mx-0 lg:mt-0 lg:max-w-none"
        >
        <div class="aspect-h-4 aspect-w-3">
        <x-img
        class="h-full w-full rounded-3xl object-cover"
        :relative="false"
        src="/images/values.jpg"
        alt="values"
        />
        </div>
        </div>
        </div>
        </div>
        </section>
    --}}

    <x-bottom-cta class="-mt-40" />
</x-site-layout>
