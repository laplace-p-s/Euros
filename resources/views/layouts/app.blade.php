<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <script>
        // テーマ初期化（フラッシュ防止のため<head>より前に実行）
        (function() {
            var theme = localStorage.getItem('theme');
            if (!theme) {
                theme = 'light';
                localStorage.setItem('theme', 'light');
            }
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="shortcut icon" type="image/x-icon"  href="{{ asset('/favicon.ico') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-700">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            @include('layouts.footer')
        </div>
        @include('layouts.top_button')
        {{-- data-tooltip属性を持つ要素のホバー/タップで表示する吹き出し(resources/js/tooltip.js) --}}
        <div id="tooltip" role="tooltip" class="fixed z-50 hidden max-w-xs px-2.5 py-1 text-[13px] leading-[18px] text-red-600 bg-white border border-red-600 rounded-lg shadow-lg pointer-events-none"></div>
    </body>
</html>
