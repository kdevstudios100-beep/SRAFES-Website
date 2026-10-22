<footer
    class="space-y-8 divide-y divide-purple-400/20 bg-yellow-100 px-4 pt-16 sm:px-6 sm:pt-20 lg:px-8"
>
    <!-- Top section: columns -->
    <div
        class="sm:gapy-12 mx-auto grid max-w-md gap-y-8 sm:max-w-none sm:grid-cols-2 sm:gap-x-8 md:gap-x-12 lg:max-w-screen-2xl lg:grid-cols-11 lg:gap-8 xl:gap-12"
    >
        <!-- Column 1 -->
        <div class="flex flex-col lg:col-span-4 lg:mx-auto">
            <!-- Logo -->
            <div class="flex items-center">
                <div class="w-60 flex-shrink-0 flex-grow-0">
                    <a href="/">
                        <img
                            src="/images/logo.png"
                            alt="Bright"
                            class="h-auto"
                        />
                    </a>
                </div>
            </div>

            <!-- Mission statement -->
            <div class="mt-6 text-lg text-purple-800">
                {{ __('site/footer.description') }}
            </div>

            <!-- Social links -->
            <div class="mt-5 w-full lg:mt-6">
                <div class="flex justify-start space-x-4">
                    @if ($siteSettings['instagram_url'])
                        <!-- Instagram -->
                        <a
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500 text-white duration-300 ease-in-out hover:bg-purple-600"
                            href="{{ $siteSettings['instagram_url'] }}"
                        >
                            <!-- TablerIcon name: brand-instagram -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
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
                                <rect
                                    x="4"
                                    y="4"
                                    width="16"
                                    height="16"
                                    rx="4"
                                />
                                <circle cx="12" cy="12" r="3" />
                                <line x1="16.5" y1="7.5" x2="16.5" y2="7.501" />
                            </svg>
                        </a>
                    @endif

                    @if ($siteSettings['facebook_url'])
                        <!-- Facebook -->
                        <a
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500 text-white duration-300 ease-in-out hover:bg-purple-600"
                            href="{{ $siteSettings['facebook_url'] }}"
                        >
                            <!-- TablerIcon name: brand-facebook -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
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

                    @if ($siteSettings['x_url'])
                        <!-- Twitter -->
                        <a
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500 text-white transition duration-300 ease-in-out hover:bg-purple-600"
                            href="{{ $siteSettings['x_url'] }}"
                        >
                            <!-- TablerIcon name: brand-twitter -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
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
                </div>
            </div>
        </div>

        <!-- Column 2 -->
        {{--
            <div class="flex-shrink sm:order-3 lg:order-none lg:col-span-2">
            <h6
            class="relative text-xl font-bold tracking-wide text-purple-900"
            >
            <span class="relative z-20">Programs</span>
            <span
            class="absolute -bottom-1 left-0 z-10 h-1 w-12 rounded-lg bg-gradient-to-r from-yellow-400 to-yellow-500"
            ></span>
            </h6>
            
            <!-- Program links -->
            <ul class="mt-6 divide-y divide-purple-400/20 text-lg">
            <li
            class="pb-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
            >
            <a href="program.html">Toddler Program</a>
            </li>
            <li
            class="py-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
            >
            <a href="program.html">Preschool</a>
            </li>
            <li
            class="py-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
            >
            <a href="program.html">Kindergarten</a>
            </li>
            <li
            class="py-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
            >
            <a href="program.html">Elementary School</a>
            </li>
            <li
            class="pt-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
            >
            <a href="program.html">Summer Camp</a>
            </li>
            </ul>
            </div>
        --}}

        <!-- Column 3 -->
        <div class="flex-shrink sm:order-4 lg:order-none lg:col-span-2">
            <h6
                class="relative text-xl font-bold tracking-wide text-purple-900"
            >
                <span class="relative z-20">
                    {{ __('site/footer.site_links_section_title') }}
                </span>
                <span
                    class="absolute -bottom-1 left-0 z-10 h-1 w-12 rounded-lg bg-gradient-to-r from-yellow-400 to-yellow-500"
                ></span>
            </h6>

            <!-- Site links -->
            <ul class="mt-6 divide-y divide-purple-400/20 text-lg">
                <li
                    class="pb-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
                >
                    <a href="/">
                        {{ __('site/navigation.home.label') }}
                    </a>
                </li>
                <li
                    class="py-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
                >
                    <a href="/about">
                        {{ __('site/navigation.about.label') }}
                    </a>
                </li>
                <li
                    class="py-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
                >
                    <a href="/gallery">
                        {{ __('site/navigation.gallery.label') }}
                    </a>
                </li>
                <li
                    class="py-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
                >
                    <a href="/about#team">
                        {{ __('site/navigation.staff.label') }}
                    </a>
                </li>
                <li
                    class="py-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
                >
                    <a href="/scholarships">
                        {{ __('site/navigation.scholarships.label') }}
                    </a>
                </li>
                {{--
                    <li
                    class="py-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
                    >
                    <a href="parents.html">Parents</a>
                    </li>
                --}}
                <li
                    class="py-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
                >
                    <a href="/careers">
                        {{ __('site/navigation.careers.label') }}
                    </a>
                </li>
                <li
                    class="pt-2 font-medium text-purple-700 duration-300 ease-in-out hover:text-purple-600"
                >
                    <a href="/contact">
                        {{ __('site/navigation.contact.label') }}
                    </a>
                </li>
            </ul>
        </div>

        <!-- Column 4 -->
        <div class="sm:order-2 lg:order-none lg:col-span-3 lg:mx-auto">
            <h6
                class="relative text-xl font-bold tracking-wide text-purple-900"
            >
                <span class="relative z-20">
                    {{ __('site/footer.contact_us_section_title') }}
                </span>
                <span
                    class="absolute -bottom-1 left-0 z-10 h-1 w-12 rounded-lg bg-gradient-to-r from-yellow-400 to-yellow-500"
                ></span>
            </h6>

            <!-- Contact information -->
            <ul class="mt-6 flex flex-col space-y-5">
                <!-- Address -->
                <li class="flex max-w-xs flex-shrink">
                    <div>
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-yellow-400"
                        >
                            <!-- TablerIcon name: map-pin -->
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
                                <circle cx="12" cy="11" r="3" />
                                <path
                                    d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z"
                                />
                            </svg>
                        </span>
                    </div>
                    <div class="mt-0 ml-3 flex-1 xl:ml-4">
                        <h5
                            class="flex items-center text-base font-semibold text-purple-900"
                        >
                            {{ __('site/header.address.label') }}
                        </h5>
                        <p
                            class="text-opacity-90 mt-0.5 text-sm leading-relaxed text-purple-800"
                        >
                            {{ $siteSettings['address'] }}
                        </p>
                    </div>
                </li>

                <!-- Email -->
                <li class="flex flex-shrink-0">
                    <div>
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-purple-200"
                        >
                            <!-- TablerIcon name: mail -->
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

                <!-- Email -->
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
                                width="24"
                                height="24"
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
            </ul>
        </div>
    </div>

    <!-- Bottom section -->
    <div
        class="mx-auto flex max-w-md flex-col justify-between py-8 sm:max-w-none sm:flex-row lg:max-w-screen-2xl"
    >
        <!-- Copyright note -->
        <span class="text-base text-purple-800/90">
            © {{ date('Y') }} {{ $siteSettings['name'] }}.
            {{ __('site/footer.all_rights_reserved_title') }}
        </span>

        <p class="mt-0.5 flex items-center text-purple-800/90">
            Made with
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="mx-1 h-5 w-5"
                width="44"
                height="44"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                fill="none"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path
                    d="M3 14c.83 .642 2.077 1.017 3.5 1c1.423 .017 2.67 -.358 3.5 -1c.83 -.642 2.077 -1.017 3.5 -1c1.423 -.017 2.67 .358 3.5 1"
                />
                <path d="M8 3a2.4 2.4 0 0 0 -1 2a2.4 2.4 0 0 0 1 2" />
                <path d="M12 3a2.4 2.4 0 0 0 -1 2a2.4 2.4 0 0 0 1 2" />
                <path d="M3 10h14v5a6 6 0 0 1 -6 6h-2a6 6 0 0 1 -6 -6v-5z" />
                <path d="M16.746 16.726a3 3 0 1 0 .252 -5.555" />
            </svg>
            <span>
                by
                <a
                    class="text-purple-700 hover:text-purple-900 hover:underline"
                    href="https://mansoor.dev"
                    target="_blank"
                >
                    Mansoor Khan
                </a>
            </span>
        </p>
    </div>
</footer>
