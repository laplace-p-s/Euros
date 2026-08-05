<x-app-layout>
    <script type="module">
        $(function () {
            {{-- アラート閉じる --}}
            $('#alert-btn').on('click', function () {
                $('#alert-div').hide();
            });

            {{-- セッションメッセージの表示 --}}
            @if(session('message'))
                $('.alert-1-text').text('{{ session('message') }}');
                $('#alert-div').show();
            @endif
        });
    </script>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-400 leading-tight">
            設定 - 基本設定
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
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('settings.general_save') }}">
                        @csrf

                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                <i class="fas fa-calendar-alt"></i>&nbsp;年度設定
                            </h3>
                            <div class="mb-4">
                                <label for="fiscal_year_start_month" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">年度開始月</label>
                                <select id="fiscal_year_start_month" name="fiscal_year_start_month" class="select-normal w-32">
                                    @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $settings->fiscal_year_start_month == $m ? 'selected' : '' }}>{{ $m }}月</option>
                                    @endfor
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">年度の起算月を設定します（通常は4月）</p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                <i class="fas fa-umbrella-beach"></i>&nbsp;有休設定
                            </h3>
                            <div class="mb-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="paid_leave_auto_grant" value="0">
                                    <input type="checkbox" name="paid_leave_auto_grant" value="1" class="sr-only peer" {{ $settings->paid_leave_auto_grant ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-gray-500 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">有休自動付与</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">年度開始時にページアクセスした際、確認の上で自動付与します</p>
                            </div>
                            <div class="mb-4">
                                <label for="paid_leave_grant_days" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">有休自動付与日数</label>
                                <input type="number" id="paid_leave_grant_days" name="paid_leave_grant_days" step="0.5" min="0" value="{{ $settings->paid_leave_grant_days }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-32 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">年度ごとに自動付与される有休の日数</p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                <i class="fas fa-calendar-day"></i>&nbsp;年次休暇設定
                            </h3>
                            <div class="mb-4">
                                <label for="annual_leave_grant_days" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">年次休暇付与日数</label>
                                <input type="number" id="annual_leave_grant_days" name="annual_leave_grant_days" step="0.5" min="0" value="{{ $settings->annual_leave_grant_days }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-32 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">年度ごとに付与される年次休暇の日数（0の場合は付与されません）</p>
                            </div>
                        </div>

                        <button type="submit" class="btn-blue">
                            <i class="fas fa-save"></i>&nbsp;保存
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
