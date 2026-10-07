<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-50 shadow-xs">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-400" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        <i class="ti ti-home text-base"></i>&nbsp;{{ __('Home') }}
                    </x-nav-link>
                </div>
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('search')" :active="request()->routeIs('search') || request()->routeIs('search.post') || request()->routeIs('detail')">
                        <i class="ti ti-calendar-event text-base"></i>&nbsp;{{ __('Search') }}
                    </x-nav-link>
                </div>
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('tools')" :active="request()->routeIs('tools') || request()->routeIs('leave*') || request()->routeIs('transit*')">
                        <i class="ti ti-tools text-base"></i>&nbsp;{{ __('Tools') }}
                    </x-nav-link>
                </div>
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*') || request()->routeIs('settings.index')">
                        <i class="ti ti-settings text-base"></i>&nbsp;{{ __('Settings') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Theme Toggle + Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Dark/Light Mode Toggle -->
                <button id="theme-toggle-desktop" onclick="toggleTheme()" class="mr-3 p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-hidden transition ease-in-out duration-150" title="テーマ切替">
                    <!-- Sun icon (shown in light mode) -->
                    <i id="theme-icon-sun-desktop" class="ti ti-sun text-xl"></i>
                    <!-- Moon icon (shown in dark mode) -->
                    <i id="theme-icon-moon-desktop" class="ti ti-moon text-xl hidden"></i>
                </button>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 focus:outline-hidden transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <i class="ti ti-chevron-down text-base"></i>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="ti ti-user"></i>&nbsp;{{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <i class="ti ti-logout"></i>&nbsp;{{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Theme Toggle (Mobile) + Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button onclick="toggleTheme()" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-hidden transition duration-150 ease-in-out mr-1" title="テーマ切替">
                    <i id="theme-icon-sun-mobile" class="ti ti-sun text-xl"></i>
                    <i id="theme-icon-moon-mobile" class="ti ti-moon text-xl hidden"></i>
                </button>
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:bg-gray-800 focus:text-gray-500 transition duration-150 ease-in-out">
                    <i :class="{'hidden': open, 'inline-block': ! open }" class="ti ti-menu-2 text-2xl inline-block"></i>
                    <i :class="{'hidden': ! open, 'inline-block': open }" class="ti ti-x text-2xl hidden"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                <i class="ti ti-home"></i>&nbsp;{{ __('Home') }}
            </x-responsive-nav-link>
        </div>
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('search')" :active="request()->routeIs('search') || request()->routeIs('search.post') || request()->routeIs('detail')">
                <i class="ti ti-calendar-event"></i>&nbsp;{{ __('Search') }}
            </x-responsive-nav-link>
        </div>
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('tools')" :active="request()->routeIs('tools') || request()->routeIs('leave*') || request()->routeIs('transit*')">
                <i class="ti ti-tools"></i>&nbsp;{{ __('Tools') }}
            </x-responsive-nav-link>
        </div>
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*') || request()->routeIs('settings.index')">
                <i class="ti ti-settings"></i>&nbsp;{{ __('Settings') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <i class="ti ti-user"></i>&nbsp;{{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        <i class="ti ti-logout"></i>&nbsp;{{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleTheme() {
        var isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeIcons();
    }

    function updateThemeIcons() {
        var isDark = document.documentElement.classList.contains('dark');

        // Desktop icons
        var sunDesktop = document.getElementById('theme-icon-sun-desktop');
        var moonDesktop = document.getElementById('theme-icon-moon-desktop');
        if (sunDesktop && moonDesktop) {
            sunDesktop.classList.toggle('hidden', isDark);
            moonDesktop.classList.toggle('hidden', !isDark);
        }

        // Mobile icons
        var sunMobile = document.getElementById('theme-icon-sun-mobile');
        var moonMobile = document.getElementById('theme-icon-moon-mobile');
        if (sunMobile && moonMobile) {
            sunMobile.classList.toggle('hidden', isDark);
            moonMobile.classList.toggle('hidden', !isDark);
        }
    }

    // 初期表示時にアイコンを正しく設定
    document.addEventListener('DOMContentLoaded', updateThemeIcons);
</script>
