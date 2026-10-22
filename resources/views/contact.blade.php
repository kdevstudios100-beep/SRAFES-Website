<x-site-layout>
    <x-slot:hero
        class="bg-gradient-to-b from-white via-purple-25 to-white"
    >
        <section class="overflow-hidden px-4 pb-12 sm:px-6 lg:px-8 lg:pt-24">
            <!-- Contact information container -->
            <div class="mx-auto max-w-xl lg:max-w-screen-xl">
                <!-- Section header -->
                <div class="flex items-center">
                    <h2 class="h2 max-w-4xl text-purple-900">
                        {{ __('site/contact.heading') }}
                    </h2>
                </div>

                <div class="mt-3 flex items-center sm:mt-4">
                    <p
                        class="text-lg text-purple-800 text-opacity-90 sm:text-xl"
                    >
                        {{ __('site/contact.description') }}
                    </p>
                </div>

                <!-- Contact information cards -->
                <div
                    class="mt-12 grid grid-cols-1 gap-4 sm:mt-14 sm:grid-cols-4 sm:gap-6 lg:mt-20 lg:grid-cols-4 xl:gap-12"
                >
                    <!-- Address card -->
                    <div
                        class="rounded-3xl bg-yellow-200 px-4 py-8 sm:col-span-2 sm:p-8 lg:col-span-1"
                    >
                        <div class="flex sm:flex-col lg:flex-row">
                            <div>
                                <span
                                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-yellow-400"
                                >
                                    <!-- TablerIcon name: map-pin -->
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-8 w-8 text-purple-700"
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
                            <div
                                class="ml-6 flex-1 sm:ml-0 sm:mt-3 lg:ml-6 lg:mt-0"
                            >
                                <h5
                                    class="flex items-center text-xl font-semibold text-purple-900"
                                >
                                    {{ __('site/contact.address_label') }}
                                </h5>
                                <p
                                    class="mt-1.5 text-base leading-relaxed text-purple-800"
                                >
                                    {{ $siteSettings['address'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Email card -->
                    <div
                        class="rounded-3xl bg-purple-50 px-4 py-8 sm:col-span-2 sm:p-8 sm:py-10 lg:col-span-1"
                    >
                        <div class="flex sm:flex-col lg:flex-row">
                            <div>
                                <span
                                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-200"
                                >
                                    <!-- TablerIcon name: mail -->
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-8 w-8 text-purple-700"
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
                            <div
                                class="ml-6 flex-1 sm:ml-0 sm:mt-3 lg:ml-6 lg:mt-0"
                            >
                                <h5
                                    class="flex items-center text-xl font-semibold text-purple-900"
                                >
                                    {{ __('site/contact.email_label') }}
                                </h5>
                                <p
                                    class="mt-1.5 text-base leading-relaxed text-purple-800"
                                >
                                    {{ $siteSettings['email'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Phone number card -->
                    <div
                        class="rounded-3xl bg-rose-50 px-4 py-8 sm:col-span-2 sm:p-8 sm:py-10 lg:col-span-1"
                    >
                        <div class="flex sm:flex-col lg:flex-row">
                            <div>
                                <span
                                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-200"
                                >
                                    <!-- TablerIcon name: phone -->
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-8 w-8 text-purple-700"
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
                            <div
                                class="ml-6 flex-1 sm:ml-0 sm:mt-3 lg:ml-6 lg:mt-0"
                            >
                                <h5
                                    class="flex items-center text-xl font-semibold text-purple-900"
                                >
                                    {{ __('site/contact.phone_label') }}
                                </h5>
                                <p
                                    class="mt-1.5 text-base leading-relaxed text-purple-800"
                                >
                                    {{ $siteSettings['phone'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Phone number card -->
                    <div
                        class="rounded-3xl bg-purple-50 px-4 py-8 sm:col-span-2 sm:p-8 sm:py-10 lg:col-span-1"
                    >
                        <div class="flex sm:flex-col lg:flex-row">
                            <div>
                                <span
                                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-200"
                                >
                                    <!-- TablerIcon name: phone -->
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        class="h-8 w-8 text-purple-700"
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
                            <div
                                class="ml-6 flex-1 sm:ml-0 sm:mt-3 lg:ml-6 lg:mt-0"
                            >
                                <h5
                                    class="flex items-center text-xl font-semibold text-purple-900"
                                >
                                    {{ __('site/contact.whatsapp_label') }}
                                </h5>
                                <p
                                    class="mt-1.5 text-base leading-relaxed text-purple-800"
                                >
                                    {{ $siteSettings['whatsapp'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </x-slot>

    <section class="relative w-full bg-white px-4 sm:px-6 lg:px-8">
        <!-- Google map widget -->
        <div class="mt-16 rounded-3xl lg:mx-auto lg:mt-24 lg:max-w-screen-xl">
            <iframe
                class="w-full rounded-3xl"
                src="{{ $siteSettings['google_maps_embed_url'] }}"
                height="600"
                style="border: 0"
                allowfullscreen=""
                loading="lazy"
            ></iframe>
        </div>
    </section>

    <x-bottom-cta />
</x-site-layout>
