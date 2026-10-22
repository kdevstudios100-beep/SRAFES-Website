<div {{ $attributes }}>
    <div class="aspect-h-2 aspect-w-3 sm:aspect-h-4 sm:aspect-w-3">
        <img
            class="rounded-3xl object-cover shadow-md"
            src="{{ $profileImage }}"
            alt="{{ $name }}'s profile image"
        />
    </div>

    <div class="mt-3.5 pl-2 text-lg sm:pl-0 sm:text-center">
        <p class="font-semibold tracking-wide text-purple-800">
            {{ $name }}
        </p>

        <p class="text-base font-medium text-purple-600">
            {{ $tagLine }}
        </p>
    </div>
</div>
