<div>
    <div
        class="relative z-10 mx-auto mb-8 w-full rounded-xl bg-white p-8 shadow"
    >
        <form class="flex gap-10">
            <div class="grow">
                <label
                    for="admission_number"
                    class="ml-0.5 text-sm font-medium text-purple-900"
                >
                    Roll Number *
                </label>

                <input
                    wire:model.live.debounce.750ms="admission_number"
                    id="admission_number"
                    type="text"
                    placeholder="Roll Number"
                    class="mt-2 h-14 w-full rounded-2xl border-2 border-purple-50 p-4 text-sm font-medium text-purple-700 placeholder-purple-700 placeholder-opacity-70 outline-none duration-300 ease-in-out focus:border-purple-200 focus:outline-none focus:ring-purple-200"
                    required
                />
            </div>
        </form>
    </div>

    @if (filled($this->student))
        {{ $this->table }}
    @elseif ($rateLimited)
        <div class="rounded-xl bg-white p-6 text-center shadow">
            <p class="text-purple-700">
                Too many attempts. Please wait a few minutes and try again.
            </p>
        </div>
    @endif
</div>
