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
                    <i aria-hidden="true" class="ti ti-info-circle shrink-0 text-xl text-blue-700 dark:text-blue-800"></i>
                    <span class="sr-only">Info</span>
                    <div class="alert-1-text"></div>
                    <button id="alert-btn" type="button" class="alert-1-close" aria-label="Close">
                        <span class="sr-only">Close</span>
                        <i aria-hidden="true" class="ti ti-x text-xl"></i>
                    </button>
                </div>
            </div>
            {{-- Infoエリア --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xs dark:shadow-xs sm:rounded-lg">
                <div class="p-4 text-gray-900">
                    <button type="button" onclick="location.href='{{ route('leave') }}'" class="mr-0! mb-0! btn-blue w-36"><i class="ti ti-beach text-base"></i>&nbsp;{{ __('Paid Leave') }}</button>
                    <span class="mt-1 ml-2 text-sm text-gray-600 dark:text-gray-400">有休・年次休暇・代休の使用履歴を管理</span>
                </div>
                <div class="p-4 text-gray-900">
                    <button type="button" onclick="location.href='{{ route('transit') }}'" class="mr-0! mb-0! btn-blue w-36"><i class="ti ti-train text-base"></i>&nbsp;{{ __('Transit Expense') }}</button>
                    <span class="mt-1 ml-2 text-sm text-gray-600 dark:text-gray-400">月ごとの交通費を明細で管理</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
