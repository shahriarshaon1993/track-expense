<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex">

        <!-- ================= LEFT SIDE ================= -->
        <div class="hidden lg:flex w-1/2 relative items-center justify-center bg-gray-900 text-white">

            <!-- Background Image -->
            <div class="absolute inset-0">
                <img src="{{ $image ?? 'https://images.unsplash.com/photo-1640161704729-cbe966a08476' }}"
                     class="w-full h-full object-cover opacity-15">
            </div>

            <!-- Overlay Content -->
            <div class="relative z-10 max-w-lg px-10 text-white">

                <!-- Small Label -->
                <span class="inline-block mb-4 text-sm tracking-widest uppercase text-indigo-300 font-semibold">
                    Finance Platform
                </span>

                <!-- Title -->
                <h1 class="text-5xl font-extrabold leading-tight mb-6">
                    Welcome Back<span class="text-indigo-400">.</span>
                </h1>

                <!-- Description -->
                <p class="text-lg text-gray-200 leading-relaxed mb-8">
                    Take full control of your financial journey with powerful insights,
                    smart analytics, and seamless management — all in one place.
                </p>

                <!-- Feature Highlights -->
                <div class="space-y-3 text-sm text-gray-300">
                    <div class="flex items-center space-x-3">
                        <span class="w-2 h-2 bg-indigo-400 rounded-full"></span>
                        <span>Real-time financial tracking</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="w-2 h-2 bg-indigo-400 rounded-full"></span>
                        <span>Smart budgeting & analytics</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="w-2 h-2 bg-indigo-400 rounded-full"></span>
                        <span>Secure and reliable platform</span>
                    </div>
                </div>

            </div>
        </div>


        <!-- ================= RIGHT SIDE ================= -->
        <div class="flex w-full lg:w-1/2 items-center justify-center bg-white dark:bg-gray-800 px-6 py-12">

            <div class="w-full max-w-md">
                <div class="relative">
                    <!-- Glow Effect -->
                    <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 opacity-20 blur-xl rounded-2xl"></div>
                    <!-- Main Card -->
                    <div class="relative bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl border border-gray-200 dark:border-gray-700 shadow-2xl rounded-2xl p-8">
                        <!-- Divider -->
                        <div class="flex items-center mb-6">
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                            <span class="px-3 text-xs text-gray-400 uppercase tracking-wider">
                                Secure Access
                            </span>
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                        </div>

                        <!-- SLOT CONTENT -->
                        <div class="space-y-5">
                            {{ $slot }}
                        </div>

                    </div>
                </div>
        </div>

    </div>
</body>
</html>
