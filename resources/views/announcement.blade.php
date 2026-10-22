<x-site-layout>
    <x-slot:hero class="bg-purple-25">
        <section class="px-4 pt-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-screen-xl pb-20 pt-5 md:mt-10">
                <div class="relative">
                    <h2
                        class="h1 mx-auto max-w-3xl text-center text-purple-900"
                    >
                        {{ $announcement->title }}
                    </h2>
                    <p
                        class="mx-auto mt-4 max-w-2xl text-center text-xl leading-relaxed text-purple-800 sm:mt-5"
                    >
                        {{ $announcement->description }}
                    </p>

                    <div
                        class="prose mx-auto mt-12 max-w-3xl prose-figcaption:hidden prose-img:rounded-xl"
                    >
                        {!! $announcement->content !!}
                    </div>
                </div>
            </div>
        </section>
    </x-slot>

    <!-- Gradient transition -->
    <div
        class="h-32 w-full bg-gradient-to-b from-purple-25 to-white sm:h-40 lg:h-44"
    ></div>

    <x-bottom-cta class="-mt-40" />
</x-site-layout>
