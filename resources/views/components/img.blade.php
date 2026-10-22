@props(['relative' => true])

@auth
    @php
        $mediaAsset = \App\Models\MediaAsset::query()
            ->where('filename', $attributes->get('src'))
            ->orWhere('filename', str($attributes->get('src'))->after('images/'))
            ->first();
    @endphp

    <div @class([$attributes->get('class'), 'relative' => $relative, 'group'])>
        <img {{ $attributes }} />

        <div
            class="absolute inset-0 hidden items-center justify-center rounded-2xl group-hover:flex"
        >
            <a
                href="{{ \App\Filament\Resources\MediaAssets\MediaAssetResource::getUrl('edit', ['record' => $mediaAsset]) }}"
                target="_blank"
                class="btn-xs z-50 me-2 bg-purple-600 text-sm font-medium text-white opacity-100"
            >
                <x-heroicon-m-photo class="me-2 size-4" />
                Change Image
            </a>
        </div>
    </div>
@else
    <img {{ $attributes }} />
@endauth
