<header>
    <div class="hidden px-4 sm:px-6 lg:block">
        <!-- Container -->
        <div
            class="relative mx-auto max-w-screen-xl border-b border-purple-200/30 py-5"
        >
            <div class="flex items-center justify-between">
                <!-- Site branding -->
                <div class="w-60 flex-shrink-0 flex-grow-0">
                    <a href="/">
                        <img
                            src="/images/logo.png"
                            alt="Bright"
                            class="h-auto"
                        />
                    </a>
                </div>

                <!-- Contact information -->
                <ul class="ml-8 flex lg:space-x-6 xl:space-x-16">
                    <!-- Email -->
                    <li class="flex flex-shrink-0">
                        <div>
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-yellow-400"
                            >
                                <!-- TablerIcon name: mail -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-purple-700"
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
                                    <rect
                                        x="3"
                                        y="5"
                                        width="18"
                                        height="14"
                                        rx="2"
                                    />
                                    <polyline points="3 7 12 13 21 7" />
                                </svg>
                            </span>
                        </div>
                        <div class="ml-3 flex-1 xl:ml-4">
                            <h5
                                class="flex items-center text-base font-semibold text-purple-900"
                            >
                                {{ __('site/header.email.label') }}
                            </h5>
                            <p
                                class="text-opacity-90 mt-0.5 text-sm leading-relaxed text-purple-800"
                            >
                                {{ $siteSettings['email'] }}
                            </p>
                        </div>
                    </li>

                    <!-- Phone number -->
                    <li class="flex flex-shrink-0">
                        <div>
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-200"
                            >
                                <!-- TablerIcon name: phone -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-purple-700"
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
                                        d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2"
                                    />
                                </svg>
                            </span>
                        </div>
                        <div class="ml-3 flex-1 xl:ml-4">
                            <h5
                                class="flex items-center text-base font-semibold text-purple-900"
                            >
                                {{ __('site/header.phone.label') }}
                            </h5>
                            <p
                                class="text-opacity-90 mt-0.5 text-sm leading-relaxed text-purple-800"
                            >
                                {{ $siteSettings['phone'] }}
                            </p>
                        </div>
                    </li>

                    <li class="flex flex-shrink-0">
                        <div>
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-purple-200"
                            >
                                <!-- TablerIcon name: phone -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    class="h-6 w-6 text-purple-700"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                >
                                    <path
                                        d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9"
                                    ></path>
                                    <path
                                        d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1"
                                    ></path>
                                </svg>
                            </span>
                        </div>
                        <div class="ml-3 flex-1 xl:ml-4">
                            <h5
                                class="flex items-center text-base font-semibold text-purple-900"
                            >
                                {{ __('site/header.whatsapp.label') }}
                            </h5>
                            <p
                                class="text-opacity-90 mt-0.5 text-sm leading-relaxed text-purple-800"
                            >
                                {{ $siteSettings['whatsapp'] }}
                            </p>
                        </div>
                    </li>

                    {{-- TODO: not working --}}
                    <!-- Language Switcher -->
                    {{--
                        <li class="flex flex-shrink-0">
                        @include(
                        'translation-manager::language-switcher',
                        [
                        'otherLanguages' => ($availableLocales = config(
                        'translation-manager.available_locales',
                        )),
                        'currentLanguage' => collect($availableLocales)->firstWhere(
                        'code',
                        app()->getLocale(),
                        ),
                        'showFlags' => (bool) config('translation-manager.show_flags'),
                        ]
                        )
                        </li>
                    --}}
                </ul>
            </div>
        </div>
    </div>

    <div class="px-4 sm:px-6">
        <nav class="mx-auto flex max-w-screen-xl items-center pt-5">
            <div class="flex w-full items-center justify-between">
                <!-- Main navigation menu for large screens -->
                <div
                    class="hidden items-center justify-between md:space-x-6 lg:flex lg:space-x-10"
                >
                    <a href="/">
                        <div class="group relative p-0.5">
                            <span
                                class="relative z-10 text-lg font-medium text-purple-700 duration-300 ease-in-out group-hover:text-purple-600"
                            >
                                {{ __('site/navigation.home.label') }}
                            </span>
                            <span
                                class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                            ></span>
                        </div>
                    </a>
                    <a href="/about">
                        <div class="group relative p-0.5">
                            <span
                                class="relative z-10 text-lg font-medium text-purple-700 duration-300 ease-in-out group-hover:text-purple-600"
                            >
                                {{ __('site/navigation.about.label') }}
                            </span>
                            <span
                                class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                            ></span>
                        </div>
                    </a>
                    <a href="/gallery">
                        <div class="group relative p-0.5">
                            <span
                                class="relative z-10 text-lg font-medium text-purple-700 duration-300 ease-in-out group-hover:text-purple-600"
                            >
                                {{ __('site/navigation.gallery.label') }}
                            </span>
                            <span
                                class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                            ></span>
                        </div>
                    </a>
                    <a href="/about#team">
                        <div class="group relative p-0.5">
                            <span
                                class="relative z-10 text-lg font-medium text-purple-700 duration-300 ease-in-out group-hover:text-purple-600"
                            >
                                {{ __('site/navigation.staff.label') }}
                            </span>
                            <span
                                class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                            ></span>
                        </div>
                    </a>

                    <!-- Programs dropdown container -->
                    {{--
                        <div class="relative" x-data="{ open: false }">
                        <!-- Programs dropdown button -->
                        <button
                        type="button"
                        aria-expanded="false"
                        @click="open = !open"
                        class="outline-none focus:outline-none"
                        >
                        <div class="group relative p-0.5">
                        <span
                        class="relative z-10 flex items-center text-lg font-medium duration-300 ease-in-out group-hover:text-purple-600"
                        :class="open ? 'text-purple-600' : 'text-purple-700'"
                        >
                        Programs
                        
                        <!-- Heroicon name: solid/chevron-down -->
                        <!-- Toggle class 'rotate-180' on dropdown open and close -->
                        <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        class="ml-1.5 h-5 w-5 transform duration-300 ease-in-out"
                        :class="{'rotate-180': open}"
                        >
                        <path
                        fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                        clip-rule="evenodd"
                        />
                        </svg>
                        </span>
                        </div>
                        </button>
                        
                        <!-- Programs dropdown -->
                        <div
                        class="absolute left-1/2 z-20 mt-3 w-screen max-w-xs -translate-x-1/2 rounded-2xl border border-gray-50 bg-white p-4 shadow-lg outline-none focus:outline-none"
                        x-show="open"
                        x-transition:enter="transition duration-300 ease-out"
                        x-transition:enter-start="-translate-y-5 opacity-0"
                        x-transition:enter-end="translate-y-0 opacity-100"
                        x-transition:leave="transition duration-200 ease-in"
                        x-transition:leave-start="translate-y-0 opacity-100"
                        x-transition:leave-end="-translate-y-5 opacity-0"
                        style="display: none"
                        >
                        <div
                        @click.away="open = false"
                        @keydown.escape.window="open = false"
                        >
                        <a
                        href="program.html"
                        class="group block w-full rounded-xl py-4 transition duration-200 ease-in-out hover:bg-purple-25 sm:p-5"
                        >
                        <h5
                        class="text-lg font-semibold text-purple-600"
                        >
                        Toddler Program
                        </h5>
                        <p
                        class="mt-1 text-sm text-purple-800 opacity-90"
                        >
                        Curabitur non nulla sit amet nisl tempu
                        convallis quis ac lectus.
                        </p>
                        </a>
                        <hr class="my-1 border-purple-200/30 sm:my-2" />
                        <a
                        href="program.html"
                        class="group block w-full rounded-xl py-4 transition duration-200 ease-in-out hover:bg-purple-25 sm:p-5"
                        >
                        <h5
                        class="text-lg font-semibold text-purple-600"
                        >
                        Preschool
                        </h5>
                        <p
                        class="mt-1 text-sm text-purple-800 opacity-90"
                        >
                        Curabitur non nulla sit amet nisl tempu
                        convallis quis ac lectus.
                        </p>
                        </a>
                        <hr class="my-1 border-purple-200/30 sm:my-2" />
                        <a
                        href="program.html"
                        class="group block w-full rounded-xl py-4 transition duration-200 ease-in-out hover:bg-purple-25 sm:p-5"
                        >
                        <h5
                        class="text-lg font-semibold text-purple-600"
                        >
                        Kindergarten
                        </h5>
                        <p
                        class="mt-1 text-sm text-purple-800 opacity-90"
                        >
                        Curabitur non nulla sit amet nisl tempu
                        convallis quis ac lectus.
                        </p>
                        </a>
                        <hr class="my-1 border-purple-200/30 sm:my-2" />
                        <a
                        href="program.html"
                        class="group block w-full rounded-xl py-4 transition duration-200 ease-in-out hover:bg-purple-25 sm:p-5"
                        >
                        <h5
                        class="text-lg font-semibold text-purple-600"
                        >
                        Elementary School
                        </h5>
                        <p
                        class="mt-1 text-sm text-purple-800 opacity-90"
                        >
                        Curabitur non nulla sit amet nisl tempu
                        convallis quis ac lectus.
                        </p>
                        </a>
                        <hr class="my-1 border-purple-200/30 sm:my-2" />
                        <a
                        href="program.html"
                        class="group block w-full rounded-xl py-4 transition duration-200 ease-in-out hover:bg-purple-25 sm:p-5"
                        >
                        <h5
                        class="text-lg font-semibold text-purple-600"
                        >
                        Summer Camp
                        </h5>
                        <p
                        class="mt-1 text-sm text-purple-800 opacity-90"
                        >
                        Curabitur non nulla sit amet nisl tempu
                        convallis quis ac lectus.
                        </p>
                        </a>
                        </div>
                        </div>
                        </div>
                    --}}

                    <a href="/scholarships">
                        <div class="group relative p-0.5">
                            <span
                                class="relative z-10 text-lg font-medium text-purple-700 duration-300 ease-in-out group-hover:text-purple-600"
                            >
                                {{ __('site/navigation.scholarships.label') }}
                            </span>
                            <span
                                class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                            ></span>
                        </div>
                    </a>

                    <a href="/examination-results">
                        <div class="group relative p-0.5">
                            <span
                                class="relative z-10 text-lg font-medium text-purple-700 duration-300 ease-in-out group-hover:text-purple-600"
                            >
                                {{ __('site/navigation.examination_results.label') }}
                            </span>
                            <span
                                class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                            ></span>
                        </div>
                    </a>

                    {{--
                        <a href="parents.html">
                        <div class="group relative p-0.5">
                        <span
                        class="relative z-10 text-lg font-medium text-purple-700 duration-300 ease-in-out group-hover:text-purple-600"
                        >
                        Parents
                        </span>
                        <span
                        class="absolute -left-1 -right-1 bottom-0 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                        ></span>
                        </div>
                        </a>
                    --}}
                </div>

                <!-- Call to action button -->
                <div class="hidden lg:block">
                    <a
                        class="btn bg-yellow-500 text-lg font-semibold text-purple-900 hover:bg-yellow-600"
                        href="/contact"
                    >
                        {{ __('site/navigation.contact.label') }}
                    </a>
                </div>

                <!-- Logo on smaller screens: < lg -->
                <div
                    class="block w-48 flex-shrink-0 flex-grow-0 sm:w-52 lg:hidden"
                >
                    <a href="/">
                        <img
                            src="/images/logo.png"
                            alt="Bright"
                            class="h-auto"
                        />
                    </a>
                </div>

                <!-- Mobile menu container -->
                <div
                    class="flex items-center gap-4 lg:hidden"
                    x-data="{ open: false }"
                    x-init="
                        () => {
                            $watch('open', (value) => {
                                if (value) {
                                    document.getElementById('banner').style.display = 'none'
                                } else {
                                    document.getElementById('banner').style.display = 'block'
                                }
                            })
                        }
                    "
                >
                    {{--
                        @include(
                        'translation-manager::language-switcher',
                        [
                        'otherLanguages' => ($availableLocales = config(
                        'translation-manager.available_locales',
                        )),
                        'currentLanguage' => collect($availableLocales)->firstWhere(
                        'code',
                        app()->getLocale(),
                        ),
                        'showFlags' => (bool) config('translation-manager.show_flags'),
                        ]
                        )
                    --}}
                    <!-- Hamburger menu button -->
                    <button
                        class="group relative z-50 h-5 w-6 rotate-0 transform cursor-pointer transition duration-500 ease-in-out focus:outline-none"
                        @click="open = !open"
                        aria-label="Toggle Navigation"
                    >
                        <span
                            class="absolute block h-1 rotate-0 transform rounded-full opacity-100 transition-all duration-300 ease-in-out"
                            :class="open ? 'top-2 left-1/2 w-0 bg-purple-50 group-hover:bg-white': 'top-0 left-0 w-full bg-purple-900 group-hover:bg-purple-600'"
                        ></span>
                        <span
                            class="absolute top-2 left-0 block h-1 w-full transform rounded-full bg-purple-900 opacity-100 transition-all duration-300 ease-in-out group-hover:bg-purple-600"
                            :class="open ? 'rotate-45 bg-purple-50 group-hover:bg-white': 'rotate-0 bg-purple-900 group-hover:bg-purple-600'"
                        ></span>
                        <span
                            class="absolute top-2 left-0 block h-1 w-full transform rounded-full bg-purple-900 opacity-100 transition-all duration-300 ease-in-out group-hover:bg-purple-600"
                            :class="open ? '-rotate-45 bg-purple-50 group-hover:bg-white': 'rotate-0 bg-purple-900 group-hover:bg-purple-600'"
                        ></span>
                        <span
                            class="absolute block h-1 rotate-0 transform rounded-full bg-purple-900 opacity-100 transition-all duration-300 ease-in-out group-hover:bg-purple-600"
                            :class="open ? 'top-2 left-1/2 w-0 bg-purple-50 group-hover:bg-white': 'top-4 left-0 w-full bg-purple-900 group-hover:bg-purple-600'"
                        ></span>
                    </button>

                    <!-- Mobile menu -->
                    <div
                        class="absolute top-0 left-0 z-40 w-screen overflow-y-scroll bg-gradient-to-tr from-purple-600 to-purple-600 px-4 py-16 sm:px-8"
                        @keydown.escape.window="open = false"
                        @click.away="mobileMenuOpen = false"
                        x-show="open"
                        x-transition:enter="transition duration-300 ease-out"
                        x-transition:enter-start="-translate-y-full opacity-0"
                        x-transition:enter-end="translate-y-0 opacity-100"
                        x-transition:leave="transition duration-200 ease-in"
                        x-transition:leave-start="translate-y-0 opacity-100"
                        x-transition:leave-end="-translate-y-full opacity-0"
                        style="display: none"
                    >
                        <div
                            class="flex h-full w-full flex-col items-center justify-center"
                        >
                            <!-- Site links -->
                            <div
                                class="mx-auto flex w-full flex-col items-center justify-evenly space-y-6"
                            >
                                <a href="/">
                                    <div class="group relative p-0.5">
                                        <span
                                            class="relative z-10 text-2xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                        >
                                            {{ __('site/navigation.home.label') }}
                                        </span>
                                        <span
                                            class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                        ></span>
                                    </div>
                                </a>

                                <a href="/about">
                                    <div class="group relative p-0.5">
                                        <span
                                            class="relative z-10 text-2xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                        >
                                            {{ __('site/navigation.about.label') }}
                                        </span>
                                        <span
                                            class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                        ></span>
                                    </div>
                                </a>

                                <a href="/gallery">
                                    <div class="group relative p-0.5">
                                        <span
                                            class="relative z-10 text-2xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                        >
                                            {{ __('site/navigation.gallery.label') }}
                                        </span>
                                        <span
                                            class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                        ></span>
                                    </div>
                                </a>

                                <a href="/about#team">
                                    <div class="group relative p-0.5">
                                        <span
                                            class="relative z-10 text-2xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                        >
                                            {{ __('site/navigation.staff.label') }}
                                        </span>
                                        <span
                                            class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                        ></span>
                                    </div>
                                </a>

                                <a href="/scholarships">
                                    <div class="group relative p-0.5">
                                        <span
                                            class="relative z-10 text-2xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                        >
                                            {{ __('site/navigation.scholarships.label') }}
                                        </span>
                                        <span
                                            class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                        ></span>
                                    </div>
                                </a>

                                <a href="/examination-results">
                                    <div class="group relative p-0.5">
                                        <span
                                            class="relative z-10 text-2xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                        >
                                            {{ __('site/navigation.examination_results.label') }}
                                        </span>
                                        <span
                                            class="absolute -right-1 bottom-0 -left-1 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                        ></span>
                                    </div>
                                </a>

                                {{--
                                    <a href="parents.html">
                                    <div class="group relative p-0.5">
                                    <span
                                    class="relative z-10 text-2xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                    >
                                    Parents
                                    </span>
                                    <span
                                    class="absolute -left-1 -right-1 bottom-0 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                    ></span>
                                    </div>
                                    </a>
                                --}}

                                <!-- CTA button -->
                                <a
                                    class="relative inline-flex items-center justify-center rounded-full bg-yellow-400 px-8 py-4 text-center text-lg leading-normal font-semibold text-purple-900 duration-300 ease-in-out"
                                    href="#0"
                                >
                                    {{ __('site/navigation.contact.label') }}
                                </a>
                            </div>

                            <hr
                                class="border-opacity-30 my-8 w-full border-purple-200 sm:my-10"
                            />

                            <!-- Programs links -->
                            {{--
                                <div class="mx-auto w-full max-w-md">
                                <p
                                class="text-center text-lg font-semibold uppercase tracking-wider text-purple-200 sm:text-left"
                                >
                                Programs
                                </p>
                                <div
                                class="mt-4 grid justify-items-center gap-4 sm:grid-cols-2 sm:justify-items-start sm:gap-x-8"
                                >
                                <a href="program.html">
                                <div class="group relative p-0.5">
                                <span
                                class="relative z-10 text-xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                >
                                Toddler Program
                                </span>
                                <span
                                class="absolute -left-1 -right-1 bottom-0 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                ></span>
                                </div>
                                </a>
                                
                                <a
                                class="sm:justify-self-end"
                                href="program.html"
                                >
                                <div class="group relative p-0.5">
                                <span
                                class="relative z-10 text-xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                >
                                Preschool
                                </span>
                                <span
                                class="absolute -left-1 -right-1 bottom-0 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                ></span>
                                </div>
                                </a>
                                
                                <a href="program.html">
                                <div class="group relative p-0.5">
                                <span
                                class="relative z-10 text-xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                >
                                Kindergarten
                                </span>
                                <span
                                class="absolute -left-1 -right-1 bottom-0 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                ></span>
                                </div>
                                </a>
                                
                                <a
                                class="sm:justify-self-end"
                                href="program.html"
                                >
                                <div class="group relative p-0.5">
                                <span
                                class="relative z-10 text-xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                >
                                Elementary School
                                </span>
                                <span
                                class="absolute -left-1 -right-1 bottom-0 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                ></span>
                                </div>
                                </a>
                                
                                <a href="program.html">
                                <div class="group relative p-0.5">
                                <span
                                class="relative z-10 text-xl font-medium text-purple-50 duration-300 ease-in-out group-hover:text-white"
                                >
                                Summer Camp
                                </span>
                                <span
                                class="absolute -left-1 -right-1 bottom-0 h-1.5 origin-bottom scale-x-0 transform rounded-lg bg-yellow-400 duration-300 ease-in-out group-hover:scale-x-100"
                                ></span>
                                </div>
                                </a>
                                </div>
                                </div>
                            --}}
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>
