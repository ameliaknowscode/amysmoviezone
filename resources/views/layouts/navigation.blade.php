<nav x-data="{ open: false, searchOpen: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                        <span class="text-sm font-semibold text-gray-800">{{ config('app.name') }}</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    {{-- Explore dropdown --}}
                    <div class="flex items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('movies.browse', 'recommendations', 'director-connections', 'compare.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                    {{ __('Explore') }}
                                    <svg class="ms-1 fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('movies.browse')">
                                    {{ __('Movies') }}
                                </x-dropdown-link>
                                @auth
                                <x-dropdown-link :href="route('recommendations')">
                                    {{ __('For You') }}
                                </x-dropdown-link>
                                @endauth
                                <x-dropdown-link :href="route('director-connections')">
                                    {{ __('Director Connections') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('compare.index')">
                                    {{ __('Head to Head') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">
                        {{ __('Users') }}
                    </x-nav-link>
                    @auth
                    <x-nav-link :href="route('watchlist.index')" :active="request()->routeIs('watchlist.*')">
                        {{ __('My Watchlist') }}
                    </x-nav-link>
                    <x-nav-link :href="route('stats.show')" :active="request()->routeIs('stats.*')">
                        {{ __('Stats') }}
                    </x-nav-link>
                    <x-nav-link :href="route('feed')" :active="request()->routeIs('feed')">
                        {{ __('Activity') }}
                    </x-nav-link>
                    @if(Auth::user()->is_admin)
                    <div class="flex items-center">
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.*', 'dashboard') ? 'border-indigo-400 text-gray-900 focus:border-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:text-gray-700 focus:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                {{ __('Admin') }}
                                <svg class="ms-1 fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('dashboard')">
                                {{ __('Dashboard') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.users.index')">
                                {{ __('Users') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.movies.index')">
                                {{ __('Movies') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.people.index')">
                                {{ __('People') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.genres.index')">
                                {{ __('Genres') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.genres.import')">
                                {{ __('Import Genres') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.types.index')">
                                {{ __('Types') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.credits.import')">
                                {{ __('Import Credits') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                    </div>
                    @endif
                    @endauth
                </div>
            </div>

            <!-- Search Button (desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                <button
                    @click="searchOpen = true"
                    class="inline-flex items-center gap-1 px-3 py-2 text-sm text-gray-500 hover:text-gray-700 focus:outline-none transition"
                    aria-label="Search"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                    </svg>
                    Search
                </button>
            </div>

            <!-- Settings Dropdown / Login -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        @php $unreadCount = Auth::user()->unreadNotifications()->count(); @endphp
                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div class="relative">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="h-7 w-7 rounded-full object-cover">
                                @else
                                    <div class="h-7 w-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold select-none">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                @if($unreadCount > 0)
                                    <span class="absolute -top-1 -right-1 h-3.5 w-3.5 rounded-full bg-red-500 border-2 border-white flex items-center justify-center">
                                        <span class="text-white" style="font-size: 7px; line-height: 1;">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                                    </span>
                                @endif
                            </div>
                            <div>{{ Auth::user()->name }}</div>
                            <div>
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="Auth::user()->username ? route('profile.show', Auth::user()->username) : route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('notifications.index')">
                            <span class="flex items-center justify-between gap-2">
                                Notifications
                                @if($unreadCount > 0)
                                    <span class="inline-flex items-center justify-center h-4 min-w-4 px-1 rounded-full bg-red-500 text-white text-xs font-medium leading-none">
                                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                    </span>
                                @endif
                            </span>
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @else
                <a href="{{ route('register') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition ease-in-out duration-150">
                    {{ __('Sign Up') }}
                </a>
                <a href="{{ route('login') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                    {{ __('Log In') }}
                </a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div
        x-show="searchOpen"
        x-transition.opacity
        @keydown.escape.window="searchOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        style="display: none;"
    >
        <div
            @click.stop
            x-transition.scale
            class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4"
        >
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Search movies &amp; people</h3>

            <form method="GET" action="{{ route('search') }}">
                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        name="q"
                        placeholder="Enter title or person name…"
                        x-init="$watch('searchOpen', val => { if (val) $nextTick(() => $el.focus()) })"
                        class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                        </svg>
                        Search
                    </button>
                </div>
            </form>

            <div class="mt-4 text-right">
                <button @click="searchOpen = false" class="text-sm text-gray-500 hover:text-gray-700">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <button
                @click="open = false; searchOpen = true"
                class="w-full text-left block ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none transition"
            >
                Search
            </button>
            <div class="px-4 pt-2 pb-1 text-xs font-semibold uppercase tracking-widest text-gray-400">Explore</div>
            <x-responsive-nav-link :href="route('movies.browse')" :active="request()->routeIs('movies.browse')">
                {{ __('Movies') }}
            </x-responsive-nav-link>
            @auth
            <x-responsive-nav-link :href="route('recommendations')" :active="request()->routeIs('recommendations')">
                {{ __('For You') }}
            </x-responsive-nav-link>
            @endauth
            <x-responsive-nav-link :href="route('director-connections')" :active="request()->routeIs('director-connections')">
                {{ __('Director Connections') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('compare.index')" :active="request()->routeIs('compare.*')">
                {{ __('Head to Head') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">
                {{ __('Users') }}
            </x-responsive-nav-link>
            @auth
            <div class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-widest text-gray-400">My Zone</div>
            <x-responsive-nav-link :href="route('watchlist.index')" :active="request()->routeIs('watchlist.*')">
                {{ __('My Watchlist') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('stats.show')" :active="request()->routeIs('stats.*')">
                {{ __('Stats') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('feed')" :active="request()->routeIs('feed')">
                {{ __('Activity') }}
            </x-responsive-nav-link>
            @if(Auth::user()->is_admin)
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                {{ __('Admin') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="pl-8">
                {{ __('— Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="pl-8">
                {{ __('— Users') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.movies.index')" :active="request()->routeIs('admin.movies.*')" class="pl-8">
                {{ __('— Movies') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.people.index')" :active="request()->routeIs('admin.people.*')" class="pl-8">
                {{ __('— People') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.genres.index')" :active="request()->routeIs('admin.genres.*')" class="pl-8">
                {{ __('— Genres') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.types.index')" :active="request()->routeIs('admin.types.*')" class="pl-8">
                {{ __('— Types') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.credits.import')" :active="request()->routeIs('admin.credits.import')" class="pl-8">
                {{ __('— Import Credits') }}
            </x-responsive-nav-link>
            @endif
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            @auth
            <div class="px-4 flex items-center gap-3">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="h-10 w-10 rounded-full object-cover shrink-0">
                @else
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-bold select-none shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="Auth::user()->username ? route('profile.show', Auth::user()->username) : route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
            @else
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('register')">
                    {{ __('Sign Up') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('login')">
                    {{ __('Log In') }}
                </x-responsive-nav-link>
            </div>
            @endauth
        </div>
    </div>
</nav>
