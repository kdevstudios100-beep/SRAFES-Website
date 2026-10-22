<x-site-layout>
    <x-slot:hero
        class="bg-gradient-to-b from-purple-25 to-purple-50"
    >
        <section class="px-4 pt-16 sm:px-6 lg:px-8">
            <!-- Hero container -->
            <div
                class="mx-auto max-w-screen-xl lg:grid lg:grid-cols-12 lg:gap-8"
                x-data="{ modalOpen: false }"
            >
                <!-- Hero text content -->
                <div
                    class="flex flex-col items-center justify-center lg:col-span-6 lg:items-start"
                >
                    <div>
                        <span
                            class="inline-block -rotate-1 rounded-full bg-purple-200 px-4 py-2 font-medium text-purple-700 shadow-md"
                        >
                            {{ __('site/home-page.tagline') }}
                        </span>
                    </div>
                    <h1
                        class="h1 mt-4 max-w-xl text-center text-purple-900 sm:mt-5 lg:max-w-none lg:text-left"
                    >
                        {{ __('site/home-page.heading') }}
                    </h1>
                    <p
                        class="mt-3 max-w-2xl text-center text-xl leading-loose text-purple-800 lg:text-left"
                    >
                        {{ __('site/home-page.content') }}
                    </p>

                    <!-- Hero buttons -->
                    <div
                        class="mt-8 flex flex-col items-center overflow-hidden sm:flex-row"
                    >
                        <a
                            href="/about"
                            class="btn group bg-yellow-500 text-lg font-semibold text-purple-900 hover:bg-yellow-600"
                        >
                            {{ __('site/home-page.actions.about.label') }}

                            <!-- Tablericon name: arrow-narrow-right -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="ml-3 h-6 w-6 group-hover:animate-horizontal-bounce"
                                width="44"
                                height="44"
                                viewBox="0 0 24 24"
                                stroke-width="2"
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
                                <line x1="5" y1="12" x2="19" y2="12" />
                                <line x1="15" y1="16" x2="19" y2="12" />
                                <line x1="15" y1="8" x2="19" y2="12" />
                            </svg>
                        </a>

                        @if (filled($siteSettings['home_page_video_url']))
                            <button
                                class="btn group mt-6 bg-purple-200 text-lg font-medium text-purple-900 hover:bg-purple-600 hover:text-white sm:ml-6 sm:mt-0"
                                @click="modalOpen = true"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="mr-3 h-7 w-7 text-purple-600 duration-300 ease-in-out group-hover:text-purple-50"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    fill="none"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        stroke="none"
                                        d="M0 0h24v24H0z"
                                        fill="none"
                                    ></path>
                                    <path
                                        d="M6 4v16a1 1 0 0 0 1.524 .852l13 -8a1 1 0 0 0 0 -1.704l-13 -8a1 1 0 0 0 -1.524 .852z"
                                        stroke-width="0"
                                        fill="currentColor"
                                    ></path>
                                </svg>

                                {{ __('site/home-page.actions.watch_video.label') }}
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Hero image & video -->
                <div
                    class="mx-auto mt-16 flex max-w-3xl flex-col justify-center lg:col-span-6 lg:mt-0 lg:max-w-none"
                >
                    <div class="group relative">
                        <x-img
                            class="h-auto w-full"
                            src="/images/hero-home.png"
                            alt="Bright Photo Collage"
                        />

                        @if ($siteSettings['home_page_video_url'])
                            <div
                                class="absolute inset-0 flex items-center justify-center"
                            >
                                <span
                                    class="absolute inline-flex h-20 w-20 animate-ping rounded-full bg-purple-400 opacity-60"
                                ></span>

                                    <!-- Video modal button -->
                                    <button
                                        class="group relative z-10 flex h-20 w-20 items-center justify-center rounded-full bg-purple-600/90 outline-none duration-300 ease-in-out hover:bg-purple-600/95"
                                        @click="modalOpen = true"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-12 w-12 text-white/90 duration-300 ease-in-out group-hover:text-white/95"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            stroke-width="2"
                                            stroke="currentColor"
                                            fill="none"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path
                                                stroke="none"
                                                d="M0 0h24v24H0z"
                                                fill="none"
                                            ></path>
                                            <path
                                                d="M6 4v16a1 1 0 0 0 1.524 .852l13 -8a1 1 0 0 0 0 -1.704l-13 -8a1 1 0 0 0 -1.524 .852z"
                                                stroke-width="0"
                                                fill="currentColor"
                                            ></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                </div>

                <!-- Video modal-->
                <div
                    class="fixed inset-0 z-10 h-full w-full overflow-hidden px-4 transition duration-300 ease-linear"
                    aria-modal="true"
                    x-show="modalOpen"
                    @keydown.window.escape="modalOpen = false"
                    style="display: none"
                >
                    <!-- Overlay-->
                    <div
                        class="fixed inset-0 bg-black bg-opacity-50"
                        x-show="modalOpen"
                        x-transition:enter="transition duration-300 ease-out"
                        x-transition:enter-start="bg-opacity-0"
                        x-transition:enter-end="bg-opacity-50"
                        x-transition:leave="transition duration-200 ease-in"
                        x-transition:leave-start="bg-opacity-50"
                        x-transition:leave-end="bg-opacity-0"
                    ></div>

                    <!-- Panel -->
                    <div
                        class="mx-auto flex min-h-screen w-auto items-center justify-center"
                        x-show="modalOpen"
                        x-transition:enter="transition duration-300 ease-out"
                        x-transition:enter-start="translate-y-24 scale-95 opacity-0"
                        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                        x-transition:leave="transition duration-200 ease-out"
                        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                        x-transition:leave-end="translate-y-24 scale-95 opacity-0"
                    >
                        <div
                            class="max-h-full w-full max-w-6xl overflow-auto rounded-2xl bg-white"
                            @click.away="modalOpen = false"
                        >
                            <div class="aspect-h-9 aspect-w-16 relative">
                                <iframe
                                    class="absolute h-full w-full"
                                    src="{{ $siteSettings['home_page_video_url'] }}"
                                    title="Video"
                                    webkitAllowFullScreen
                                    mozallowfullscreen
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visible only on sm screens ( <= 640px ) and lg screens ( >= 1024px	< 1280px ) -->
            <div
                class="mt-20 flex flex-col items-center sm:hidden lg:mt-24 lg:flex xl:hidden"
            >
                <!-- Social proof -->
                <p
                    class="text-sm font-semibold uppercase tracking-wider text-purple-900"
                >
                    Rates 5 stars by over
                    <span class="font-semibold text-purple-600">
                        100 parents
                    </span>
                </p>
                <div
                    class="mt-8 flex flex-col divide-y divide-purple-400/20 overflow-hidden sm:flex-row sm:divide-x sm:divide-y-0"
                >
                    <div
                        class="flex flex-col items-center pb-5 sm:pb-0 sm:pr-10"
                    >
                        <div class="flex w-full justify-center space-x-1">
                            <!-- 1st TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 2nd TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 3rd TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 4th TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 5th TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>
                        </div>
                        <p
                            class="mt-2.5 text-xs font-bold uppercase tracking-wide text-purple-700"
                        >
                            Great Schools
                        </p>
                    </div>
                    <div
                        class="flex flex-col items-center py-5 sm:px-10 sm:py-0"
                    >
                        <div class="flex w-full justify-center space-x-0.5">
                            <!-- 1st TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 2nd TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 3rd TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 4th TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 5th TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>
                        </div>
                        <p
                            class="mt-2.5 text-xs font-bold uppercase tracking-wide text-purple-700"
                        >
                            Private School Review
                        </p>
                    </div>
                    <div
                        class="flex flex-col items-center pt-5 sm:pl-10 sm:pt-0"
                    >
                        <div class="flex w-full justify-center space-x-0.5">
                            <!-- 1st TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 2nd TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 3rd TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 4th TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>

                            <!-- 5th TablerIcon star-filled -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-[18px] w-[18px] text-yellow-500 lg:h-5 lg:w-5"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <path
                                    d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z"
                                    stroke-width="0"
                                    fill="currentColor"
                                ></path>
                            </svg>
                        </div>
                        <p
                            class="mt-2.5 text-xs font-bold uppercase tracking-wide text-purple-700"
                        >
                            Google Reviews
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </x-slot>

    <!-- Gradient transition -->
    <div
        class="h-40 w-full bg-gradient-to-b from-purple-50 to-yellow-100 sm:h-48 xl:h-52"
    ></div>

    <!-- School marketing section (features and objections) -->
    <section
        class="overflow-hidden bg-yellow-100 px-4 pb-16 sm:px-6 sm:pb-24 lg:px-8"
    >
        <!-- Container -->
        <div class="mx-auto max-w-screen-xl">
            <!-- Centered content with feature list -->
            <div class="relative">
                <!-- Block title and subtext -->
                <h2 class="h2 mx-auto max-w-4xl text-center text-purple-900">
                    {{ __('site/home-page.school_marketing_section.heading') }}
                </h2>
                <p
                    class="mx-auto mt-4 max-w-3xl text-center text-xl leading-relaxed text-purple-800"
                >
                    {{ __('site/home-page.school_marketing_section.content') }}
                </p>

                @if (__('site/home-page.school_marketing_section.checklist'))
                    <!-- Feature list -->
                    <div class="mx-auto mt-12 max-w-3xl">
                        <ul
                            class="-mx-3 -my-2 flex flex-wrap items-center justify-center text-lg text-purple-800"
                        >
                            @php
                                $list = str(__('site/home-page.school_marketing_section.checklist'))
                                    ->explode("\n")
                                    ->filter();
                            @endphp

                            @foreach ($list as $item)
                                <li class="mx-3 my-2 flex items-center">
                                    <img
                                        class="mr-3 h-7 w-7 flex-shrink-0"
                                        src="/images/checkmark.svg"
                                    />
                                    <span>{{ str($item)->trim() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- First alternating block -->
            <div
                class="mx-auto mt-16 max-w-xl sm:mt-20 lg:mt-24 lg:grid lg:max-w-none lg:grid-cols-12 lg:gap-x-14 xl:gap-x-20 2xl:gap-x-24"
            >
                <!-- Block images-->
                <div class="relative lg:col-span-6">
                    <div class="relative sm:pl-36 lg:pl-20 xl:pl-32">
                        <div class="aspect-h-4 aspect-w-3 rounded-2xl">
                            <x-img
                                class="rounded-2xl object-cover"
                                :relative="false"
                                src="/images/home-blocks-01.jpg"
                                alt="Home blocks 01"
                            />
                        </div>
                        <div
                            class="absolute bottom-0 left-0 hidden rounded-3xl sm:block sm:h-72 sm:w-72 sm:translate-y-1/3 lg:h-64 lg:w-64 xl:h-72 xl:w-72 2xl:h-80 2xl:w-80"
                        >
                            <x-img
                                class="h-full w-full rounded-3xl object-cover"
                                :relative="false"
                                src="/images/home-blocks-02.jpg"
                                alt="Home blocks 02"
                            />
                        </div>
                    </div>
                </div>

                <!-- Block text content-->
                <div
                    class="mt-16 flex flex-col justify-center sm:mt-44 lg:col-span-6 lg:mt-0"
                >
                    <div>
                        <span
                            class="inline-block -rotate-1 rounded-full bg-purple-200 px-4 py-2 font-medium text-purple-700 shadow-md"
                        >
                            {{ __('site/home-page.school_marketing_section.first_block.tagline') }}
                        </span>
                    </div>
                    <h3 class="h3 mt-4 text-purple-900 sm:mt-5">
                        {{ __('site/home-page.school_marketing_section.first_block.heading') }}
                    </h3>
                    <p
                        class="mt-3 max-w-2xl text-lg leading-loose text-purple-800"
                    >
                        {{ __('site/home-page.school_marketing_section.first_block.content') }}
                    </p>
                    <div class="mt-6">
                        <a
                            href="/about"
                            class="btn-sm group bg-purple-600 font-medium text-white hover:bg-purple-500"
                        >
                            {{ __('site/home-page.school_marketing_section.first_block.action_label') }}

                            <!-- Tablericon name: arrow-narrow-right -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="ml-3 h-5 w-5 group-hover:animate-horizontal-bounce"
                                width="44"
                                height="44"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <line x1="15" y1="16" x2="19" y2="12"></line>
                                <line x1="15" y1="8" x2="19" y2="12"></line>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Second alternating block -->
            <div
                class="mx-auto mt-16 max-w-xl sm:mt-44 lg:mt-56 lg:grid lg:max-w-none lg:grid-cols-12 lg:gap-x-14 xl:mt-60 xl:gap-x-20 2xl:mt-64 2xl:gap-x-24"
            >
                <!-- Block images -->
                <div class="relative lg:order-2 lg:col-span-6">
                    <div class="relative sm:pr-36 lg:pr-20 xl:pr-32">
                        <div class="aspect-h-4 aspect-w-3 rounded-3xl">
                            <x-img
                                class="rounded-3xl object-cover"
                                :relative="false"
                                src="/images/home-blocks-03.jpg"
                                alt="Home blocks 03"
                            />
                        </div>
                        <div
                            class="absolute right-0 top-0 hidden rounded-3xl sm:block sm:h-72 sm:w-72 sm:-translate-y-1/3 lg:h-64 lg:w-64 xl:h-72 xl:w-72 2xl:h-80 2xl:w-80"
                        >
                            <x-img
                                class="h-full w-full rounded-3xl object-cover"
                                :relative="false"
                                src="/images/home-blocks-04.jpg"
                                alt="Home blocks 04"
                            />
                        </div>
                    </div>
                </div>

                <!-- Block text content -->
                <div
                    class="mt-16 flex flex-col justify-center sm:mt-20 lg:order-1 lg:col-span-6 lg:mt-0"
                >
                    <div>
                        <span
                            class="inline-block -rotate-1 rounded-full bg-purple-200 px-4 py-2 font-medium text-purple-700 shadow-md"
                        >
                            {{ __('site/home-page.school_marketing_section.second_block.tagline') }}
                        </span>
                    </div>
                    <h3 class="h3 mt-4 text-purple-900 sm:mt-5">
                        {{ __('site/home-page.school_marketing_section.second_block.heading') }}
                    </h3>
                    <p
                        class="mt-3 max-w-2xl text-lg leading-loose text-purple-800"
                    >
                        {{ __('site/home-page.school_marketing_section.second_block.content') }}
                    </p>

                    <div class="mt-6">
                        <a
                            href="/about"
                            class="btn-sm group bg-purple-600 font-medium text-white hover:bg-purple-500"
                        >
                            {{ __('site/home-page.school_marketing_section.second_block.action_label') }}

                            <!-- Tablericon name: arrow-narrow-right -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="ml-3 h-5 w-5 group-hover:animate-horizontal-bounce"
                                width="44"
                                height="44"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    stroke="none"
                                    d="M0 0h24v24H0z"
                                    fill="none"
                                ></path>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <line x1="15" y1="16" x2="19" y2="12"></line>
                                <line x1="15" y1="8" x2="19" y2="12"></line>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- School teachers' qualifications -->
    <section class="relative w-full px-4 py-16 sm:px-6 sm:py-24 xl:px-8">
        <!-- Container -->
        <div class="mx-auto max-w-xl lg:max-w-screen-xl">
            <div
                class="grid gap-16 lg:grid-cols-2 lg:gap-12 xl:grid-cols-11 xl:gap-24"
            >
                <!-- Text content -->
                <div
                    class="flex flex-col justify-center lg:order-2 lg:col-span-1 xl:col-span-6"
                >
                    <div>
                        <span
                            class="inline-block -rotate-1 rounded-full bg-purple-200 px-4 py-2 font-medium text-purple-700 shadow-md"
                        >
                            {{ __('site/home-page.school_teachers_qualifications_section.tagline') }}
                        </span>
                    </div>
                    <h2 class="h2 mt-4 text-purple-900 sm:mt-5">
                        {{ __('site/home-page.school_teachers_qualifications_section.heading') }}
                    </h2>
                    <p
                        class="mt-4 max-w-xl text-xl leading-relaxed text-purple-800 md:mt-5"
                    >
                        {{ __('site/home-page.school_teachers_qualifications_section.content') }}
                    </p>

                    <!-- Teacher qualifications box -->
                    <div
                        class="relative mt-16 max-w-4xl rounded-xl bg-yellow-100 sm:mt-14"
                    >
                        <span
                            class="absolute -top-7 left-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-600 to-purple-600 shadow-md sm:left-10"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8 text-purple-50"
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
                                <circle cx="15" cy="15" r="3" />
                                <path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5" />
                                <path
                                    d="M10 19h-5a2 2 0 0 1 -2 -2v-10c0 -1.1 .9 -2 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -1 1.73"
                                />
                                <line x1="6" y1="9" x2="18" y2="9" />
                                <line x1="6" y1="12" x2="9" y2="12" />
                                <line x1="6" y1="15" x2="8" y2="15" />
                            </svg>
                        </span>
                        <div class="mt-2 px-4 py-10 sm:px-10 sm:py-12">
                            <p
                                class="text-lg font-semibold text-purple-900 sm:text-xl"
                            >
                                {{ __('site/home-page.school_teachers_qualifications_section.description') }}
                            </p>

                            <!-- Teacher qualifications list -->
                            <ul class="mt-5 space-y-5 text-lg text-purple-800">
                                <!-- Item 1 -->
                                @php
                                    $list = str(__('site/home-page.school_teachers_qualifications_section.qualifications'))
                                        ->explode("\n")
                                        ->filter();
                                @endphp

                                @foreach ($list as $item)
                                    <li class="flex items-center">
                                        <img
                                            class="mr-3 h-7 w-7 flex-shrink-0"
                                            src="/images/checkmark.svg"
                                        />

                                        <span>
                                            {{ str($item)->trim() }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>

                            <!-- Link to team section -->
                            <a
                                href="/about#team"
                                class="btn-sm group mt-10 bg-purple-600 font-medium text-white hover:bg-purple-500"
                            >
                                Meet the rest of the team

                                <!-- Tablericon name: arrow-narrow-right -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="ml-3 h-5 w-5 group-hover:animate-horizontal-bounce"
                                    width="44"
                                    height="44"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    fill="none"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        stroke="none"
                                        d="M0 0h24v24H0z"
                                        fill="none"
                                    ></path>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <line
                                        x1="15"
                                        y1="16"
                                        x2="19"
                                        y2="12"
                                    ></line>
                                    <line x1="15" y1="8" x2="19" y2="12"></line>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Featured teachers section -->
                <div
                    class="mx-auto grid w-full gap-10 sm:mx-0 sm:max-w-none sm:grid-cols-2 sm:gap-8 lg:order-1 lg:col-span-1 lg:mt-20 lg:gap-4 xl:col-span-5 xl:gap-8"
                >
                    @php
                    $employees = App\Models\Employee::featured()
                        ->take(4)
                        ->get();
                    @endphp

                    @foreach ($employees as $employee)
                        <x-profile-cards.simple
                            @class(['lg:-translate-y-20' => $loop->odd])
                            :profile-image="$employee->getFirstMediaUrl('avatar')"
                            :name="$employee->name"
                            :tag-line="$employee->role->getLabel()"
                        />
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- School programs section -->
    <section
        class="overflow-hidden px-4 pb-24 pt-16 sm:px-6 sm:pb-28 sm:pt-24 md:pb-0 lg:px-8"
    >
        <!-- Container -->
        <div class="mx-auto max-w-xl md:max-w-screen-xl">
            <!-- Section header title and subtext  -->
            <div class="md:grid md:grid-cols-2 md:gap-12 lg:gap-16">
                <div>
                    <h2 class="h2 max-w-4xl text-purple-900">
                        {{ __('site/home-page.school_programs_section.heading') }}
                    </h2>
                    <p
                        class="mx-auto mt-4 max-w-2xl text-xl leading-relaxed text-purple-800 sm:mt-5 lg:text-left"
                    >
                        {{ __('site/home-page.school_programs_section.description') }}
                    </p>
                </div>
            </div>

            <!-- School programs -->
            <div
                class="mt-16 md:mt-72 md:grid md:grid-cols-2 md:gap-8 lg:gap-16"
            >
                <!-- Program 1 and background dotted line decorations -->
                <div class="relative">
                    <img
                        src="/images/curved-dotted-line.svg"
                        class="absolute left-1/2 top-0 hidden -translate-y-1/2 md:block"
                        style="width: calc(50% + 4rem)"
                    />

                    <!-- Program 1 -->
                    <div
                        class="relative z-10 w-full rounded-3xl bg-purple-25 px-8 py-10 sm:p-12 md:px-8 md:py-10 lg:p-12"
                    >
                        <div class="flex flex-col justify-between">
                            <div class="flex-1">
                                <h3 class="h3 text-purple-900">
                                    {{ __('site/home-page.school_programs_section.first_program.heading') }}
                                </h3>
                                <p
                                    class="mt-3 max-w-2xl text-lg leading-loose text-purple-800"
                                >
                                    {{ __('site/home-page.school_programs_section.first_program.description') }}
                                </p>
                                <div class="relative mt-8">
                                    <x-img
                                        class="rounded-2xl object-cover"
                                        src="/images/program-01.jpg"
                                        alt="Program 01"
                                    />
                                </div>
                            </div>
                            <div class="mt-8">
                                <a
                                    href="program.html"
                                    class="btn-sm group bg-purple-600 font-medium text-white hover:bg-purple-500"
                                >
                                    {{ __('site/home-page.school_programs_section.first_program.action_label') }}

                                    <!-- Tablericon name: arrow-narrow-right -->
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="ml-3 h-5 w-5 group-hover:animate-horizontal-bounce"
                                        width="44"
                                        height="44"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        fill="none"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path
                                            stroke="none"
                                            d="M0 0h24v24H0z"
                                            fill="none"
                                        ></path>
                                        <line
                                            x1="5"
                                            y1="12"
                                            x2="19"
                                            y2="12"
                                        ></line>
                                        <line
                                            x1="15"
                                            y1="16"
                                            x2="19"
                                            y2="12"
                                        ></line>
                                        <line
                                            x1="15"
                                            y1="8"
                                            x2="19"
                                            y2="12"
                                        ></line>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <img
                        src="/images/looped-dotted-line.svg"
                        class="absolute bottom-0 left-1/2 hidden translate-y-[90%] md:block"
                        style="width: calc(50% + 4rem)"
                    />
                </div>

                <!-- Program 2 -->
                <div
                    class="relative mt-12 w-full rounded-3xl bg-yellow-200 px-8 py-10 sm:p-12 md:mt-0 md:-translate-y-80 md:px-8 md:py-10 lg:p-12"
                >
                    <div class="flex flex-col justify-between">
                        <div class="flex-1">
                            <h3 class="h3 text-purple-900">
                                {{ __('site/home-page.school_programs_section.second_program.heading') }}
                            </h3>
                            <p
                                class="mt-3 max-w-2xl text-lg leading-loose text-purple-800"
                            >
                                {{ __('site/home-page.school_programs_section.second_program.description') }}
                            </p>
                            <div class="relative mt-8">
                                <x-img
                                    class="rounded-2xl object-cover"
                                    :relative="false"
                                    src="/images/program-02.jpg"
                                    alt="Program 02"
                                />
                            </div>
                        </div>
                        <div class="mt-8">
                            <a
                                href="program.html"
                                class="btn-sm group bg-purple-600 font-medium text-white hover:bg-purple-500"
                            >
                                {{ __('site/home-page.school_programs_section.second_program.action_label') }}

                                <!-- Tablericon name: arrow-narrow-right -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="ml-3 h-5 w-5 group-hover:animate-horizontal-bounce"
                                    width="44"
                                    height="44"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    fill="none"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        stroke="none"
                                        d="M0 0h24v24H0z"
                                        fill="none"
                                    ></path>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <line
                                        x1="15"
                                        y1="16"
                                        x2="19"
                                        y2="12"
                                    ></line>
                                    <line x1="15" y1="8" x2="19" y2="12"></line>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Program 3 -->
                <div
                    class="relative mt-12 w-full rounded-3xl bg-blue-50 px-8 py-10 sm:p-12 md:col-start-2 md:mt-0 md:-translate-y-80 md:px-8 md:py-10 lg:p-12"
                >
                    <div class="flex flex-col justify-between">
                        <div class="flex-1">
                            <h3 class="h3 text-purple-900">
                                {{ __('site/home-page.school_programs_section.third_program.heading') }}
                            </h3>
                            <p
                                class="mt-3 max-w-2xl text-lg leading-loose text-purple-800"
                            >
                                {{ __('site/home-page.school_programs_section.third_program.description') }}
                            </p>
                            <div class="relative mt-8">
                                <x-img
                                    class="rounded-2xl object-cover"
                                    :relative="false"
                                    src="/images/program-03.jpg"
                                    alt="Program 03"
                                />
                            </div>
                        </div>
                        <div class="mt-8">
                            <a
                                href="program.html"
                                class="btn-sm group bg-purple-600 font-medium text-white hover:bg-purple-500"
                            >
                                {{ __('site/home-page.school_programs_section.third_program.action_label') }}

                                <!-- Tablericon name: arrow-narrow-right -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="ml-3 h-5 w-5 group-hover:animate-horizontal-bounce"
                                    width="44"
                                    height="44"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    fill="none"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        stroke="none"
                                        d="M0 0h24v24H0z"
                                        fill="none"
                                    ></path>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <line
                                        x1="15"
                                        y1="16"
                                        x2="19"
                                        y2="12"
                                    ></line>
                                    <line x1="15" y1="8" x2="19" y2="12"></line>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Frequently asked questions -->
    <!--
      This example uses Alpine.js for the accordion effect in FAQ. If you're
      not familiar with Alpine you can check out their documentation
      here: https://alpinejs.dev/start-here
    -->
    {{--
        <section class="bg-yellow-100 py-20 sm:py-28 md:-mt-48">
        <!-- Container -->
        <div class="mx-auto px-4 sm:px-6 lg:max-w-screen-lg lg:px-8">
        <!-- Section header title and subtext  -->
        <div class="max-w-2xl">
        <h2 class="h2 text-purple-900">Frequently asked questions</h2>
        <p
        class="mt-4 max-w-2xl text-xl leading-relaxed text-purple-800 lg:text-left"
        >
        Vestibulum ac diam sit amet quam vehicula elementum sed sit
        amet dui. Curabitur arcu erat, accumsan id imperdiet et,
        porttitor at sem.
        </p>
        </div>

        <!-- FAQ -->
        <ul class="relative mt-12 space-y-6">
        <!-- Decorator images-->
        <div>
        <img
        class="absolute -left-60 top-10 hidden h-auto w-28 2xl:block"
        src="/images/question-mark.svg"
        alt=""
        />
        <img
        class="absolute -right-60 bottom-10 hidden h-auto w-28 2xl:block"
        src="/images/bulb.svg"
        alt=""
        />
        </div>

        <!-- Question / Answer 1 -->
        <li
        class="w-full rounded-3xl bg-white px-5 py-6 sm:px-12 sm:py-8"
        x-data="{ open: false }"
        >
        <!-- Question -->
        <button
        class="group flex w-full items-center justify-between text-lg sm:text-xl"
        @click="open = !open"
        >
        <span
        class="text-left font-medium text-purple-900 duration-300 ease-in-out group-hover:text-purple-600"
        >
        What are your hours and schedules?
        </span>

        <!-- TablerIcon chevron-down -->
        <svg
        xmlns="http://www.w3.org/2000/svg"
        class="ml-3 h-6 w-6 flex-shrink-0 text-purple-700 duration-300 ease-in-out group-hover:text-purple-600 sm:ml-6"
        :class="{'rotate-180': open}"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        stroke-width="2"
        stroke="currentColor"
        fill="none"
        stroke-linecap="round"
        stroke-linejoin="round"
        >
        <path
        stroke="none"
        d="M0 0h24v24H0z"
        fill="none"
        ></path>
        <path d="M6 9l6 6l6 -6"></path>
        </svg>
        </button>

        <!-- Answer -->
        <div
        class="relative max-h-0 overflow-hidden duration-500"
        x-ref="answer1"
        x-bind:style="open ? 'max-height: ' + $refs.answer1.scrollHeight + 'px' : ''"
        >
        <div
        class="mt-3 text-base leading-relaxed text-purple-800 sm:text-lg"
        >
        Praesent sapien massa, convallis a pellentesque nec,
        egestas non nisi. Nulla quis lorem ut libero
        malesuada feugiat. Curabitur arcu erat, accumsan id
        imperdiet et, porttitor at sem. Donec sollicitudin
        molestie malesuada.
        </div>
        </div>
        </li>

        <!-- Question / Answer 2 -->
        <li
        class="w-full rounded-3xl bg-white px-5 py-6 sm:px-12 sm:py-8"
        x-data="{ open: false }"
        >
        <!-- Question -->
        <button
        class="group flex w-full items-center justify-between text-lg sm:text-xl"
        @click="open = !open"
        >
        <span
        class="text-left font-medium text-purple-900 duration-300 ease-in-out group-hover:text-purple-600"
        >
        What is your Discipline Policy?
        </span>
        <svg
        xmlns="http://www.w3.org/2000/svg"
        class="ml-3 h-6 w-5 flex-shrink-0 text-purple-700 duration-300 ease-in-out group-hover:text-purple-600 sm:ml-6 sm:h-6 sm:w-6"
        :class="{'rotate-180': open}"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        >
        <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M19 9l-7 7-7-7"
        />
        </svg>
        </button>

        <!-- Answer -->
        <div
        class="relative max-h-0 overflow-hidden duration-500"
        x-ref="answer2"
        x-bind:style="open ? 'max-height: ' + $refs.answer2.scrollHeight + 'px' : ''"
        >
        <div
        class="mt-3 text-base leading-relaxed text-purple-800 sm:text-lg"
        >
        Praesent sapien massa, convallis a pellentesque nec,
        egestas non nisi. Nulla quis lorem ut libero
        malesuada feugiat. Curabitur arcu erat, accumsan id
        imperdiet et, porttitor at sem. Donec sollicitudin
        molestie malesuada.
        </div>
        </div>
        </li>

        <!-- Question / Answer 3 -->
        <li
        class="w-full rounded-3xl bg-white px-5 py-6 sm:px-12 sm:py-8"
        x-data="{ open: false }"
        >
        <!-- Question -->
        <button
        class="group flex w-full items-center justify-between text-lg sm:text-xl"
        @click="open = !open"
        >
        <span
        class="text-left font-medium text-purple-900 duration-300 ease-in-out group-hover:text-purple-600"
        >
        Do you accommodate allergies and dietary
        restrictions?
        </span>
        <svg
        xmlns="http://www.w3.org/2000/svg"
        class="ml-3 h-6 w-5 flex-shrink-0 text-purple-700 duration-300 ease-in-out group-hover:text-purple-600 sm:ml-6 sm:h-6 sm:w-6"
        :class="{'rotate-180': open}"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        >
        <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M19 9l-7 7-7-7"
        />
        </svg>
        </button>

        <!-- Answer -->
        <div
        class="relative max-h-0 overflow-hidden duration-500"
        x-ref="answer3"
        x-bind:style="open ? 'max-height: ' + $refs.answer3.scrollHeight + 'px' : ''"
        >
        <div
        class="mt-3 text-base leading-relaxed text-purple-800 sm:text-lg"
        >
        Praesent sapien massa, convallis a pellentesque nec,
        egestas non nisi. Nulla quis lorem ut libero
        malesuada feugiat. Curabitur arcu erat, accumsan id
        imperdiet et, porttitor at sem. Donec sollicitudin
        molestie malesuada.
        </div>
        </div>
        </li>

        <!-- Question / Answer 4 -->
        <li
        class="w-full rounded-3xl bg-white px-5 py-6 sm:px-12 sm:py-8"
        x-data="{ open: false }"
        >
        <!-- Question -->
        <button
        class="group flex w-full items-center justify-between text-lg sm:text-xl"
        @click="open = !open"
        >
        <span
        class="text-left font-medium text-purple-900 duration-300 ease-in-out group-hover:text-purple-600"
        >
        Do you offer any transportation options?
        </span>
        <svg
        xmlns="http://www.w3.org/2000/svg"
        class="ml-3 h-6 w-5 flex-shrink-0 text-purple-700 duration-300 ease-in-out group-hover:text-purple-600 sm:ml-6 sm:h-6 sm:w-6"
        :class="{'rotate-180': open}"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        >
        <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M19 9l-7 7-7-7"
        />
        </svg>
        </button>

        <!-- Answer -->
        <div
        class="relative max-h-0 overflow-hidden duration-500"
        x-ref="answer4"
        x-bind:style="open ? 'max-height: ' + $refs.answer4.scrollHeight + 'px' : ''"
        >
        <div
        class="mt-3 text-base leading-relaxed text-purple-800 sm:text-lg"
        >
        Praesent sapien massa, convallis a pellentesque nec,
        egestas non nisi. Nulla quis lorem ut libero
        malesuada feugiat. Curabitur arcu erat, accumsan id
        imperdiet et, porttitor at sem. Donec sollicitudin
        molestie malesuada.
        </div>
        </div>
        </li>

        <!-- Question / Answer 5 -->
        <li
        class="w-full rounded-3xl bg-white px-5 py-6 sm:px-12 sm:py-8"
        x-data="{ open: false }"
        >
        <!-- Question -->
        <button
        class="group flex w-full items-center justify-between text-lg sm:text-xl"
        @click="open = !open"
        >
        <span
        class="text-left font-medium text-purple-900 duration-300 ease-in-out group-hover:text-purple-600"
        >
        Do you welcome parent involvement? Can I stop by
        unannounced?
        </span>
        <svg
        xmlns="http://www.w3.org/2000/svg"
        class="ml-3 h-6 w-5 flex-shrink-0 text-purple-700 duration-300 ease-in-out group-hover:text-purple-600 sm:ml-6 sm:h-6 sm:w-6"
        :class="{'rotate-180': open}"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        >
        <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M19 9l-7 7-7-7"
        />
        </svg>
        </button>

        <!-- Answer -->
        <div
        class="relative max-h-0 overflow-hidden duration-500"
        x-ref="answer5"
        x-bind:style="open ? 'max-height: ' + $refs.answer5.scrollHeight + 'px' : ''"
        >
        <div
        class="mt-3 text-base leading-relaxed text-purple-800 sm:text-lg"
        >
        Praesent sapien massa, convallis a pellentesque nec,
        egestas non nisi. Nulla quis lorem ut libero
        malesuada feugiat. Curabitur arcu erat, accumsan id
        imperdiet et, porttitor at sem. Donec sollicitudin
        molestie malesuada.
        </div>
        </div>
        </li>

        <!-- Question / Answer 6 -->
        <li
        class="w-full rounded-3xl bg-white px-5 py-6 sm:px-12 sm:py-8"
        x-data="{ open: false }"
        >
        <!-- Question -->
        <button
        class="group flex w-full items-center justify-between text-lg sm:text-xl"
        @click="open = !open"
        >
        <span
        class="text-left font-medium text-purple-900 duration-300 ease-in-out group-hover:text-purple-600"
        >
        What is your children-to-teacher ratio?
        </span>
        <svg
        xmlns="http://www.w3.org/2000/svg"
        class="ml-3 h-6 w-5 flex-shrink-0 text-purple-700 duration-300 ease-in-out group-hover:text-purple-600 sm:ml-6 sm:h-6 sm:w-6"
        :class="{'rotate-180': open}"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        >
        <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M19 9l-7 7-7-7"
        />
        </svg>
        </button>

        <!-- Answer -->
        <div
        class="relative max-h-0 overflow-hidden duration-500"
        x-ref="answer6"
        x-bind:style="open ? 'max-height: ' + $refs.answer6.scrollHeight + 'px' : ''"
        >
        <div
        class="mt-3 text-base leading-relaxed text-purple-800 sm:text-lg"
        >
        Praesent sapien massa, convallis a pellentesque nec,
        egestas non nisi. Nulla quis lorem ut libero
        malesuada feugiat. Curabitur arcu erat, accumsan id
        imperdiet et, porttitor at sem. Donec sollicitudin
        molestie malesuada.
        </div>
        </div>
        </li>
        </ul>
        </div>
        </section>
    --}}

    <x-stats />

    <x-bottom-cta />
</x-site-layout>
