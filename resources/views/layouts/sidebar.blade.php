<aside :class="{ '-translate-x-full': !open }" class="fixed z-30 inset-y-0 left-0 w-64 transform bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition duration-200 ease-in-out md:translate-x-0 md:static md:inset-0">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 border-b dark:border-gray-700">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <x-application-logo class="h-8 w-auto text-gray-800 dark:text-white" />
            <span class="text-lg font-bold text-gray-800 dark:text-gray-200">Fin Track</span>
        </a>
    </div>

    <!-- Menu -->
    @include('layouts.navigation')
</aside>
