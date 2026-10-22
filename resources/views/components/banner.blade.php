<div class="z-[60] w-full">
    @foreach (App\Models\Announcement::today()->get() as $announcement)
        @php
            $key = "isVisible{$announcement->getKey()}";
        @endphp

        <div
            x-data="{ {{ $key }}: $persist(true).using(sessionStorage) }"
            x-show="{{ $key }}"
            x-cloak
            class="border-b-2 border-yellow-800 bg-yellow-500 px-4 py-4 sm:px-6 lg:px-8"
        >
            <div
                class="mx-auto flex w-full max-w-screen-xl flex-col justify-between md:flex-row"
            >
                <div class="mb-4 md:mb-0 md:me-4">
                    <h2
                        class="mb-1 text-base font-semibold text-purple-900 dark:text-white"
                    >
                        {{ $announcement->title }}
                    </h2>
                    <p
                        class="flex items-center text-base font-normal text-purple-800"
                    >
                        {{ $announcement->description }}
                    </p>
                </div>
                <div class="flex flex-shrink-0 items-center">
                    <a
                        href="{{ route('announcement.show', $announcement) }}"
                        class="btn-xs group me-2 bg-purple-600 text-xs font-medium text-white hover:bg-purple-500"
                    >
                        <svg
                            class="me-2 h-3 w-3"
                            aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor"
                            viewBox="0 0 20 18"
                        >
                            <path
                                d="M9 1.334C7.06.594 1.646-.84.293.653a1.158 1.158 0 0 0-.293.77v13.973c0 .193.046.383.134.55.088.167.214.306.366.403a.932.932 0 0 0 .5.147c.176 0 .348-.05.5-.147 1.059-.32 6.265.851 7.5 1.65V1.334ZM19.707.653C18.353-.84 12.94.593 11 1.333V18c1.234-.799 6.436-1.968 7.5-1.65a.931.931 0 0 0 .5.147.931.931 0 0 0 .5-.148c.152-.096.279-.235.366-.403.088-.167.134-.357.134-.55V1.423a1.158 1.158 0 0 0-.293-.77Z"
                            />
                        </svg>
                        Learn more
                    </a>
                    <button
                        @click="{{ $key }} = false"
                        type="button"
                        class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg p-1.5 text-sm text-purple-600 hover:bg-yellow-900 hover:text-purple-900"
                    >
                        <svg
                            class="h-3 w-3"
                            aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 14 14"
                        >
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                            />
                        </svg>
                        <span class="sr-only">Close banner</span>
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>
