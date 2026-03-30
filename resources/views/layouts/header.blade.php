<header class="flex items-center justify-between h-16 px-6 bg-white dark:bg-gray-800 border-b dark:border-gray-700">
    <!-- Mobile menu button -->
    <button @click="open = !open" class="md:hidden text-gray-500 focus:outline-none">
        ☰
    </button>

    <!-- Page Title (Dynamic) -->
    <div class="text-lg font-semibold text-gray-700 dark:text-gray-200">
        {{ $header ?? 'Dashboard' }}
    </div>

    <!-- Right Section -->
    <div class="flex items-center space-x-4">
        <!-- Dark Mode -->
        <button id="darkModeToggle"
                class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white transition">

            <!-- Moon Icon -->
            <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5 hidden"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 12.79A9 9 0 1111.21 3
            7 7 0 0021 12.79z" />
            </svg>

            <!-- Sun Icon -->
            <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5 hidden"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 3v1m0 16v1m8.66-10h-1M4.34 12h-1
            m15.36 6.36l-.7-.7M6.34 6.34l-.7-.7
            m12.02 0l-.7.7M6.34 17.66l-.7.7
            M12 8a4 4 0 100 8 4 4 0 000-8z" />
            </svg>
        </button>

        <!-- Notification -->
        <div class="relative hidden md:block">
            <button onclick="toggleDropdown()"
                    class="relative text-gray-600 dark:text-gray-300 focus:outline-none">
                🔔
                @if (Auth::user()->unreadNotifications->count() > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1 rounded-full">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                </span>
                @endif
            </button>

            <div id="notificationDropdown"
                 class="absolute right-0 mt-2 bg-white dark:bg-gray-800 shadow-md w-64 hidden rounded-lg border dark:border-gray-700">

                <div class="p-2 text-sm font-semibold border-b dark:border-gray-700">
                    Notifications
                </div>

                <div class="max-h-64 overflow-y-auto">
                    @forelse (Auth::user()->unreadNotifications as $notification)
                        <a href="{{ route('notifications.read', $notification->id) }}"
                           class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                            {{ $notification->data['message'] }}
                        </a>
                    @empty
                        <div class="p-4 text-center text-gray-500">
                            No new notifications
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- User Dropdown -->
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button
                    class="flex items-center text-sm font-medium text-gray-600 dark:text-gray-300 focus:outline-none">
                    {{ Auth::user()->name }}
                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M5.293 7.293L10 12l4.707-4.707 1.414 1.414L10 14.828l-6.414-6.414z"
                              clip-rule="evenodd" />
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">
                    Profile
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                                     onclick="event.preventDefault(); this.closest('form').submit();">
                        Logout
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggle = document.getElementById("darkModeToggle");
        const toggleMobile = document.getElementById("darkModeToggleMobile");
        const html = document.documentElement;

        const iconMoon = document.getElementById("icon-moon");
        const iconSun = document.getElementById("icon-sun");

        // Set initial theme
        if (localStorage.getItem("theme") === "dark") {
            html.classList.add("dark");
            iconSun.classList.remove("hidden"); // show sun
        } else {
            iconMoon.classList.remove("hidden"); // show moon
        }

        function updateIcons() {
            if (html.classList.contains("dark")) {
                iconMoon.classList.add("hidden");
                iconSun.classList.remove("hidden");
            } else {
                iconSun.classList.add("hidden");
                iconMoon.classList.remove("hidden");
            }
        }

        function toggleDarkMode() {
            if (html.classList.contains("dark")) {
                html.classList.remove("dark");
                localStorage.setItem("theme", "light");
            } else {
                html.classList.add("dark");
                localStorage.setItem("theme", "dark");
            }

            updateIcons();
        }

        toggle.addEventListener("click", toggleDarkMode);

        if (toggleMobile) {
            toggleMobile.addEventListener("click", toggleDarkMode);
        }

        updateIcons(); // initial call
    });

    document.addEventListener("DOMContentLoaded", function() {
        let notifBtn = document.querySelector("[onclick='toggleDropdown()']");
        let dropdown = document.getElementById("notificationDropdown");
        let notifBadge = notifBtn.querySelector("span"); // Ambil elemen angka merah

        if (!notifBtn || !dropdown) {
            console.error("Tombol atau dropdown notifikasi tidak ditemukan.");
            return;
        }

        notifBtn.addEventListener("click", function(event) {
            event.stopPropagation();
            dropdown.classList.toggle("hidden");

            if (notifBadge) {
                fetch("{{ route('notifications.markAsRead') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                            .content,
                        "Content-Type": "application/json",
                    },
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notifBadge.remove(); // Hapus angka merah setelah dibaca
                        }
                    })
                    .catch(error => console.error("Error:", error));
            }
        });

        document.addEventListener("click", function(event) {
            if (!dropdown.contains(event.target) && event.target !== notifBtn) {
                dropdown.classList.add("hidden");
            }
        });
    });
</script>
