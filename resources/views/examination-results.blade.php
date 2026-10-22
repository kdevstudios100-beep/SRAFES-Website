@pushOnce('before.head.end')
    @filamentStyles
@endpushOnce

@pushOnce('before.body.end')
    @filamentScripts
@endpushOnce

<x-site-layout>
    <x-slot:hero
        class="bg-gradient-to-b from-purple-25 to-purple-50"
    >
        <section class="overflow-hidden px-4 pb-12 sm:px-6 lg:px-8 lg:pt-24">
            <div class="mx-auto max-w-xl lg:max-w-screen-xl">
                <!-- Section header -->
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 xl:gap-32">
                    <div class="flex items-center">
                        <h2 class="h2 max-w-4xl text-purple-900">
                            {{ __('site/examination-results.heading') }}
                        </h2>
                    </div>
                </div>

                <div class="py-10">
                    <livewire:examination-results-table-component />
                </div>
            </div>
        </section>
    </x-slot>

    <x-bottom-cta />
</x-site-layout>
