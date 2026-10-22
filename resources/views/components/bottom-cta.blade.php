<section {{ $attributes->class('bg-white py-24 sm:py-32') }}>
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <!-- Sunrise image -->
        <img class="mx-auto w-72" src="/images/sunrise.svg" />

        @php
            $title = str(__('site/components/cta.title'))->explode("\n");
        @endphp

        <!-- Header -->
        <h2 class="h1 mx-auto mt-6 max-w-3xl text-center text-purple-900">
            <span class="block">
                {{ Arr::get($title, 0) }}
            </span>

            <!-- Underlined text -->
            <span class="relative block">
                <span class="relative">
                    <!-- Underline -->
                    <img
                        class="absolute inset-0 translate-y-9 transform sm:translate-y-11 xl:translate-y-14"
                        src="/images/underline-simple-light-purple.svg"
                    />
                    <span class="relative">
                        {{ Arr::get($title, 1) }}
                    </span>
                </span>
            </span>
        </h2>

        <!-- CTA button -->
        <div class="mt-12 flex justify-center xl:mt-14">
            <a
                href="/about"
                class="btn group bg-yellow-500 text-lg font-semibold text-purple-900 hover:bg-yellow-600"
            >
                {{ __('site/components/cta.action_label') }}

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
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                    <line x1="15" y1="16" x2="19" y2="12" />
                    <line x1="15" y1="8" x2="19" y2="12" />
                </svg>
            </a>
        </div>
    </div>
</section>
