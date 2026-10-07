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
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('settings.general_save') }}">
                        @csrf

                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                <i class="ti ti-calendar text-base"></i>&nbsp;年度設定
                            </h3>
                            <div class="mb-4">
                                <label for="fiscal_year_start_month" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">年度開始月</label>
                                <select id="fiscal_year_start_month" name="fiscal_year_start_month" class="select-normal w-20">
                                    @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $settings->fiscal_year_start_month == $m ? 'selected' : '' }}>{{ $m }}月</option>
                                    @endfor
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">年度の起算月を設定します（通常は4月）</p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                <i class="ti ti-beach text-base"></i>&nbsp;有休設定
                            </h3>
                            <div class="mb-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="paid_leave_auto_grant" value="0">
                                    <input type="checkbox" name="paid_leave_auto_grant" value="1" class="sr-only peer" {{ $settings->paid_leave_auto_grant ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-hidden peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-gray-500 peer-checked:bg-blue-600"></div>
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
                                <i class="ti ti-archive text-base"></i>&nbsp;失効累積設定
                            </h3>
                            <div class="mb-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="show_expired_stock" value="0">
                                    <input type="checkbox" name="show_expired_stock" value="1" class="sr-only peer" {{ $settings->show_expired_stock ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-hidden peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-gray-500 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">失効累積を表示</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">有休の失効分をストックとして累積表示し、特別時に使用できるようにします</p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                <i class="ti ti-arrows-exchange text-base"></i>&nbsp;代休設定
                            </h3>
                            <div class="mb-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="show_compensatory" value="0">
                                    <input type="checkbox" name="show_compensatory" value="1" class="sr-only peer" {{ $settings->show_compensatory ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-hidden peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-gray-500 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">代休カードを表示</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">ONにすると、サマリーに代休カードを表示します</p>
                            </div>
                            <div class="mb-4 ml-6">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="compensatory_hide_zero" value="0">
                                    <input type="checkbox" name="compensatory_hide_zero" value="1" class="sr-only peer" {{ $settings->compensatory_hide_zero ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-hidden peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-gray-500 peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">0.5日以上の時のみ表示</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">ONにすると、代休残が0.5日未満の場合はカードを非表示にします</p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                <i class="ti ti-calendar-event text-base"></i>&nbsp;年次休暇設定
                            </h3>
                            <div class="mb-4">
                                <label for="annual_leave_grant_days" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">年次休暇付与日数</label>
                                <input type="number" id="annual_leave_grant_days" name="annual_leave_grant_days" step="0.5" min="0" value="{{ $settings->annual_leave_grant_days }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-32 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">年度ごとに付与される年次休暇の日数（0の場合は付与されません）</p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-blue">
                                <i class="ti ti-device-floppy text-base"></i>&nbsp;保存
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
