<x-app-layout>
    <script type="module">
        $(function () {
            {{-- リスナイベント --}}
        });
    </script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Infoエリア --}}
            <div id="alert-div" class="hidden">
                <div class="alert-1-div" role="alert">
                    <i aria-hidden="true" class="ti ti-info-circle flex-shrink-0 text-xl text-blue-700 dark:text-blue-800"></i>
                    <span class="sr-only">Info</span>
                    <div class="alert-1-text"></div>
                    <button id="alert-btn" type="button" class="alert-1-close" aria-label="Close">
                        <span class="sr-only">Close</span>
                        <i aria-hidden="true" class="ti ti-x text-xl"></i>
                    </button>
                </div>
            </div>
            {{-- Infoエリア --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm dark:shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900">
                    <button type="button" onclick="location.href='{{ route('settings.general') }}'" class="!mr-0 !mb-0 btn-blue w-36"><i class="ti ti-settings"></i>&nbsp;基本設定</button>
                    <span class="mt-1 ml-2 text-sm text-gray-600 dark:text-gray-400">年度・有休付与等の基本設定</span>
                </div>
                <div class="p-4 text-gray-900">
                    <button type="button" onclick="location.href='{{ route('settings.holiday') }}'" class="!mr-0 !mb-0 btn-blue w-36"><i class="ti ti-calendar-check"></i>&nbsp;祝祭日設定</button>
                    <span class="mt-1 ml-2 text-sm text-gray-600 dark:text-gray-400">土日以外で祝祭日表記される日付の設定</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
