<x-app-layout>
    <script type="module">
        $(function () {
            {{-- リスナイベント --}}
        });
    </script>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-400 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Infoエリア --}}
            <div id="alert-div" class="hidden">
                <div class="alert-1-div" role="alert">
                    <svg aria-hidden="true" class="flex-shrink-0 w-5 h-5 text-blue-700 dark:text-blue-800" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    <span class="sr-only">Info</span>
                    <div class="alert-1-text"></div>
                    <button id="alert-btn" type="button" class="alert-1-close" aria-label="Close">
                        <span class="sr-only">Close</span>
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
            </div>
            {{-- Infoエリア --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm dark:shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900">
                    <button type="button" onclick="location.href='./'" class="btn-disabled w-36" disabled><i class="fas fa-cog"></i>&nbsp;基本設定</button>
                    <span class="mt-1 ml-2 text-sm text-gray-600 dark:text-gray-400">アプリの全般的な設定（開発中）</span>
                </div>
                <div class="p-4 text-gray-900">
                    <button type="button" onclick="location.href='{{ route('settings.holiday') }}'" class="!mr-0 !mb-0 btn-blue w-36"><i class="far fa-calendar-check"></i>&nbsp;祝祭日設定</button>
                    <span class="mt-1 ml-2 text-sm text-gray-600 dark:text-gray-400">土日以外で祝祭日表記される日付の設定</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
