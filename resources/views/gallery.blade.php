@php
    use App\Models\Student;
    use App\Models\SiteSetting;

    $siteSetting = SiteSetting::first();
    $galleries = $siteSetting?->gallery_collections ?? collect([]);
@endphp

@pushOnce('before.head.end')
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/css/lightgallery.css"
/>
@endPushOnce

@pushOnce('before.body.end')
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/lightgallery.umd.min.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
        const galleryGrid = document.getElementById('gallery-grid')

        if (galleryGrid) {
            lightGallery(galleryGrid, {
                selector: 'li',
                speed: 500,
            })
        }
    })
</script>
@endPushOnce

<x-site-layout>
    <x-slot:hero class="bg-purple-25">
        <section x-data="{ activeFilter: 'all' }">
            <!-- Gallery tabs -->
            <div
                class="bg-purple-25 px-4 pt-20 sm:px-6 sm:pt-28 lg:px-8 lg:pt-36"
            >
                <div class="mx-auto max-w-(--breakpoint-xl)">
                    <h3
                        class="h2 mx-auto mb-10 max-w-2xl text-center text-purple-900 sm:mb-12 md:mb-20"
                    >
                        {{ __('site/gallery.heading') }}
                    </h3>
                    <ul
                        class="-my-2 flex flex-wrap items-center justify-center space-x-2 text-sm font-medium sm:space-x-4 lg:space-x-6"
                    >
                        <li class="my-2">
                            <button
                                @click="activeFilter = 'all'"
                                :class="activeFilter === 'all' ? 'bg-purple-600 text-purple-25 hover:bg-purple-700' : 'bg-white text-purple-700 shadow-xs hover:bg-purple-100'"
                                class="inline-flex items-center justify-center rounded-full px-4 py-1.5 duration-300 ease-in-out sm:text-lg"
                            >
                                All images
                            </button>
                        </li>
                        @forelse ($galleries as $gallery)
                            <li class="my-2">
                                <button
                                    @click="activeFilter = @js($gallery['name'] ?? '')"
                                    :class="activeFilter === @js($gallery['name'] ?? '') ?'bg-purple-600 text-purple-25 hover:bg-purple-700' : 'bg-white text-purple-700 shadow-xs hover:bg-purple-100'"
                                    class="inline-flex items-center justify-center rounded-full px-4 py-1.5 duration-300 ease-in-out sm:text-lg"
                                >
                                    {{ $gallery['name'] ?? 'Gallery' }}
                                </button>
                            </li>
                        @empty
                            {{-- No galleries configured --}}
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Background for overlay effect -->
            <div class="bg-purple-25 h-56 w-full"></div>

            <!-- Gallery -->
            <div
                class="mt-12 -translate-y-56 px-4 sm:mt-16 sm:px-6 lg:mt-20 lg:px-8"
            >
                <div class="mx-auto max-w-3xl lg:max-w-(--breakpoint-xl)">
                    <ul
                        id="gallery-grid"
                        class="grid gap-y-8 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4 lg:gap-4 2xl:gap-5"
                    >
                        @php
                            $allImages = [];
                            foreach ($galleries as $gallery) {
                                $collectionName = $gallery['name'] ?? '';

                                $media = $siteSetting->getMedia($collectionName);

                                foreach ($media as $item) {
                                    $allImages[] = [
                                        'url' => $item->getUrl(),
                                        'collection' => $collectionName,
                                        'alt' => $item->name,
                                    ];
                                }
                            }
                        @endphp

                        @forelse ($allImages as $image)
                            <!-- Gallery Image -->
                            <li
                                x-show="activeFilter === 'all' || activeFilter === @js($image['collection'])"
                                class="group gallery-item relative cursor-pointer"
                                data-src="{{ $image['url'] }}"
                                data-collection="{{ $image['collection'] }}"
                            >
                                <div class="aspect-square">
                                    <img
                                        src="{{ $image['url'] }}"
                                        class="size-full rounded-2xl object-cover"
                                        alt="{{ $image['alt'] }}"
                                    />
                                    <div
                                        class="absolute inset-0 rounded-2xl bg-gray-900 opacity-0 transition duration-300 group-hover:opacity-60"
                                    ></div>
                                    <div
                                        class="absolute inset-0 flex items-center justify-center opacity-0 transition duration-300 group-hover:opacity-100"
                                    >
                                        <div
                                            class="flex h-24 w-24 items-center justify-center rounded-full bg-purple-600 opacity-80"
                                        >
                                            <x-heroicon-s-magnifying-glass-plus
                                                class="h-12 w-12 text-white"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li
                                class="col-span-full flex items-center justify-center py-12"
                            >
                                <p class="text-center text-purple-600">
                                    No gallery images available
                                </p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </section>
    </x-slot>

    <x-bottom-cta />
</x-site-layout>
