<section>
    <header>
        <h2 class="text-lg font-medium text-zinc-100">
            {{ __('Profile Privacy') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-400">
            {{ __('When your profile is private, your ratings, watchlists, and reviews are hidden from other users.') }}
        </p>
    </header>

    <form method="POST" action="{{ route('watchlist.privacy') }}" class="mt-6">
        @csrf
        @method('PATCH')

        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-zinc-300">Private profile</p>
                <p class="text-xs text-zinc-400">Hides your ratings, watchlists, and reviews</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    class="sr-only peer"
                    {{ $user->profile_private ? 'checked' : '' }}
                    onchange="this.form.querySelector('[name=profile_private]').value = this.checked ? '1' : '0'; this.form.submit();"
                >
                <input type="hidden" name="profile_private" value="{{ $user->profile_private ? '1' : '0' }}">
                <div class="w-11 h-6 bg-zinc-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-zinc-900 after:border-zinc-700 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                <span class="ms-3 text-sm text-zinc-400">{{ $user->profile_private ? 'Private' : 'Public' }}</span>
            </label>
        </div>

        @if (session('status') === 'privacy-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="mt-3 text-sm text-zinc-400"
            >{{ __('Saved.') }}</p>
        @endif
    </form>
</section>
