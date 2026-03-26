<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Email Notifications') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Choose whether to receive email alerts for activity on your account.') }}
        </p>
    </header>

    <form method="POST" action="{{ route('profile.notifications') }}" class="mt-6">
        @csrf
        @method('PATCH')

        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-700">Email notifications</p>
                <p class="text-xs text-gray-500">New followers, liked reviews, and friend activity</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    class="sr-only peer"
                    {{ $user->email_notifications ? 'checked' : '' }}
                    onchange="this.form.querySelector('[name=email_notifications]').value = this.checked ? '1' : '0'; this.form.submit();"
                >
                <input type="hidden" name="email_notifications" value="{{ $user->email_notifications ? '1' : '0' }}">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                <span class="ms-3 text-sm text-gray-500">{{ $user->email_notifications ? 'On' : 'Off' }}</span>
            </label>
        </div>

        @if (session('status') === 'notifications-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="mt-3 text-sm text-gray-600"
            >{{ __('Saved.') }}</p>
        @endif
    </form>
</section>
