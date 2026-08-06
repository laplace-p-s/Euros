<x-app-layout>
    <script type="module">
        function delete_usage(id) {
            return $.ajax({
                type: 'POST',
                url: '{{ route('leave.usage.delete') }}',
                data: { usage_id: id },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
        }
        function delete_grant(id) {
            return $.ajax({
                type: 'POST',
                url: '{{ route('leave.grant.delete') }}',
                data: { grant_id: id },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
        }
        $(function () {
            {{-- アラート閉じる --}}
            $('#alert-btn').on('click', function () {
                $('#alert-div').hide();
            });

            {{-- 使用削除 --}}
            $('.delete-usage').on('click', function () {
                if (window.confirm('この使用記録を削除します。よろしいですか？')) {
                    var row = $(this).closest('tr');
                    var id = $(this).data('id');
                    delete_usage(id).done(function (data) {
                        row.remove();
                        location.reload();
                    }).fail(function () {
                        alert('削除に失敗しました');
                    });
                }
            });

            {{-- 付与削除 --}}
            $('.delete-grant').on('click', function () {
                if (window.confirm('この付与記録を削除します。よろしいですか？')) {
                    var row = $(this).closest('tr');
                    var id = $(this).data('id');
                    delete_grant(id).done(function (data) {
                        row.remove();
                        location.reload();
                    }).fail(function () {
                        alert('削除に失敗しました');
                    });
                }
            });

            {{-- 年度セレクタ（ref_mode を保持） --}}
            $('#fiscal-year-select').on('change', function () {
                var fy = $(this).val();
                window.location.href = '{{ route('leave') }}?fiscal_year=' + fy + '&ref_mode={{ $refMode }}';
            });

            {{-- 基準日トグル（Cookieで記憶） --}}
            $('#ref-mode-today').on('click', function () {
                window.location.href = '{{ route('leave') }}?fiscal_year={{ $selectedFY }}&ref_mode=today';
            });
            $('#ref-mode-fy-end').on('click', function () {
                window.location.href = '{{ route('leave') }}?fiscal_year={{ $selectedFY }}&ref_mode=fy_end';
            });

            {{-- モーダル制御 --}}
            $('#btn-add-usage').on('click', function () {
                $('#modal-add-usage').removeClass('hidden');
            });
            $('#close-usage-modal, #cancel-usage-modal').on('click', function () {
                $('#modal-add-usage').addClass('hidden');
            });
            $('#btn-add-grant').on('click', function () {
                $('#modal-add-grant').removeClass('hidden');
            });
            $('#close-grant-modal, #cancel-grant-modal').on('click', function () {
                $('#modal-add-grant').addClass('hidden');
            });

            {{-- 付与追加モーダルのタイプ切替 --}}
            $('#grant-leave-type').on('change', function () {
                var type = $(this).val();
                if (type === 'compensatory') {
                    $('#grant-fiscal-year-group').addClass('hidden');
                } else {
                    $('#grant-fiscal-year-group').removeClass('hidden');
                }
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

            {{-- 自動付与確認バナー --}}
            @if(!empty($autoGrantNeeded))
            <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg">
                <div class="flex items-start">
                    <svg class="flex-shrink-0 w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">{{ $currentFY }}年度の自動付与</h3>
                        <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-400">
                            <p>以下の休暇を自動付与します。よろしいですか？</p>
                            <ul class="list-disc list-inside mt-1">
                                @foreach($autoGrantNeeded as $item)
                                <li>{{ $item['label'] }}: {{ $item['days'] }}日</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <form method="POST" action="{{ route('leave.auto_grant') }}" class="inline">
                                @csrf
                                <button type="submit" class="btn-green-g">
                                    <i class="fas fa-check"></i>&nbsp;付与する
                                </button>
                            </form>
                            <form method="POST" action="{{ route('leave.auto_grant.dismiss') }}" class="inline">
                                @csrf
                                <button type="submit" class="btn-red-g">
                                    <i class="fas fa-times"></i>&nbsp;今年度はしない
                                </button>
                            </form>
                        </div>
                        <p class="mt-2 text-xs text-yellow-600 dark:text-yellow-500">※ 拒否しても「付与を追加」から手動で追加できます</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- 年度セレクタ + 基準日トグル + アクションボタン --}}
            <div class="mb-4">
                <div class="flex flex-wrap items-end -mx-3">
                    <div class="w-full md:w-auto px-3 mb-3 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 dark:text-gray-400 text-xs font-bold mb-2" for="fiscal-year-select">
                            年度 <span class="normal-case font-normal">（{{ $startMonth }}月〜翌{{ $startMonth > 1 ? $startMonth - 1 : 12 }}月）</span>
                        </label>
                        <select id="fiscal-year-select" class="select-normal w-full">
                            @foreach($fiscalYears as $fy)
                            <option value="{{ $fy }}" {{ $fy == $selectedFY ? 'selected' : '' }}>{{ $fy }}年度</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="px-3 mb-3 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 dark:text-gray-400 text-xs font-bold mb-2">基準日</label>
                        <div class="inline-flex rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600">
                            <button type="button" id="ref-mode-today"
                                class="px-3 py-[7px] text-xs font-medium transition-colors {{ $refMode === 'today' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600' }}">
                                今日現在
                            </button>
                            <button type="button" id="ref-mode-fy-end"
                                class="px-3 py-[7px] text-xs font-medium border-l border-gray-300 dark:border-gray-600 transition-colors {{ $refMode === 'fy_end' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600' }}">
                                年度末
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 px-3 flex flex-wrap gap-2 justify-end self-end">
                        <button type="button" id="btn-add-usage" class="btn-blue !mb-0">
                            <i class="fas fa-plus"></i>&nbsp;休暇使用を登録
                        </button>
                        <button type="button" id="btn-add-grant" class="btn-alternative-green !mb-0">
                            <i class="fas fa-plus-circle"></i>&nbsp;付与を追加
                        </button>
                    </div>
                </div>
            </div>

            {{-- サマリーカード --}}
            <div class="grid grid-cols-1 {{ $showExpiredStock ? 'md:grid-cols-6' : 'md:grid-cols-3' }} gap-4 mb-4">
                {{-- 有休カード --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg {{ $showExpiredStock ? 'md:col-span-2' : '' }}">
                    <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900">
                                    <i class="fas fa-umbrella-beach text-blue-600 dark:text-blue-400 text-xs"></i>
                                </span>
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">有休</h3>
                            </div>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $referenceDate->format('Y/m/d') }} 現在</span>
                        </div>
                    </div>
                    <div class="px-3 py-2">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1.5">
                            {{ number_format($paidBalance['total_remaining'], 1) }}<span class="text-xs font-normal text-gray-500 dark:text-gray-400"> 日</span>
                        </div>
                        <div class="space-y-0.5 text-xs">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>今年度付与 ({{ $paidBalance['current_fy']['fiscal_year'] }})</span>
                                <span>
                                    残 <span class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($paidBalance['current_fy']['remaining'], 1) }}</span>
                                    <span class="text-gray-400">/ {{ number_format($paidBalance['current_fy']['grant_days'], 1) }}</span>
                                </span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>昨年度付与 ({{ $paidBalance['prev_fy']['fiscal_year'] }})</span>
                                <span>
                                    残 <span class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($paidBalance['prev_fy']['remaining'], 1) }}</span>
                                    <span class="text-gray-400">/ {{ number_format($paidBalance['prev_fy']['grant_days'], 1) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 年次休暇カード --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex flex-col {{ $showExpiredStock ? 'md:col-span-2' : '' }}">
                    <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 dark:bg-green-900">
                                    <i class="fas fa-calendar-day text-green-600 dark:text-green-400 text-xs"></i>
                                </span>
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">年次休暇</h3>
                            </div>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $referenceDate->format('Y/m/d') }} 現在</span>
                        </div>
                    </div>
                    <div class="px-3 py-2 flex-1 flex flex-col">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400 mb-1.5">
                            {{ number_format($annualBalance['remaining'], 1) }}<span class="text-xs font-normal text-gray-500 dark:text-gray-400"> 日</span>
                        </div>
                        <div class="mt-auto space-y-0.5 text-xs">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>残 / 付与</span>
                                <span>
                                    残 <span class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($annualBalance['remaining'], 1) }}</span>
                                    <span class="text-gray-400">/ {{ number_format($annualBalance['grant_days'], 1) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 代休カード --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex flex-col">
                    <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900">
                                    <i class="fas fa-exchange-alt text-purple-600 dark:text-purple-400 text-xs"></i>
                                </span>
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">代休</h3>
                            </div>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $referenceDate->format('Y/m/d') }} 現在</span>
                        </div>
                    </div>
                    <div class="px-3 py-2 flex-1 flex flex-col">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-1.5">
                            {{ number_format($compBalance['remaining'], 1) }}<span class="text-xs font-normal text-gray-500 dark:text-gray-400"> 日</span>
                        </div>
                        <div class="mt-auto space-y-0.5 text-xs">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>残 / 付与</span>
                                <span>
                                    残 <span class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($compBalance['remaining'], 1) }}</span>
                                    <span class="text-gray-400">/ {{ number_format($compBalance['grant_days'], 1) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 失効累積カード --}}
                @if($showExpiredStock)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex flex-col">
                    <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900">
                                    <i class="fas fa-archive text-amber-600 dark:text-amber-400 text-xs"></i>
                                </span>
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">失効累積</h3>
                            </div>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $referenceDate->format('Y/m/d') }} 現在</span>
                        </div>
                    </div>
                    <div class="px-3 py-2 flex-1 flex flex-col">
                        <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mb-1.5">
                            {{ number_format($expiredStock['remaining'], 1) }}<span class="text-xs font-normal text-gray-500 dark:text-gray-400"> 日</span>
                        </div>
                        <div class="mt-auto space-y-0.5 text-xs">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>失効合計</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($expiredStock['expired_days'], 1) }}日</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- 月別一覧 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <i class="fas fa-table"></i>&nbsp;月別一覧
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr class="border-b border-gray-300 dark:border-gray-600">
                                <th scope="col" class="py-2 px-2 bg-gray-100 dark:bg-gray-600 border-r border-gray-300 dark:border-gray-600" rowspan="3">月</th>
                                <th scope="col" class="py-2 px-2 text-center bg-blue-100 dark:bg-blue-900/30 border-b border-blue-200 dark:border-blue-800 border-r border-r-gray-300 dark:border-r-gray-600" colspan="4">有休</th>
                                <th scope="col" class="py-2 px-2 text-center bg-green-100 dark:bg-green-900/30 border-b border-green-200 dark:border-green-800 border-r border-r-gray-300 dark:border-r-gray-600" colspan="2">年次休暇</th>
                                <th scope="col" class="py-2 px-2 text-center bg-purple-100 dark:bg-purple-900/30 border-b border-purple-200 dark:border-purple-800" colspan="2">代休</th>
                            </tr>
                            <tr class="text-xs">
                                <th class="py-1 px-2 text-center bg-blue-100 dark:bg-blue-900/30 border-r border-blue-200 dark:border-blue-800" rowspan="2">使用</th>
                                <th class="py-1 px-2 text-center bg-blue-100 dark:bg-blue-900/30 border-b border-blue-200 dark:border-blue-800 border-r border-r-gray-300 dark:border-r-gray-600" colspan="3">残</th>
                                <th class="py-1 px-2 text-center bg-green-100 dark:bg-green-900/30 border-r border-green-200 dark:border-green-800" rowspan="2">使用</th>
                                <th class="py-1 px-2 text-center bg-green-100 dark:bg-green-900/30 border-r border-r-gray-300 dark:border-r-gray-600" rowspan="2">残</th>
                                <th class="py-1 px-2 text-center bg-purple-100 dark:bg-purple-900/30 border-r border-purple-200 dark:border-purple-800" rowspan="2">使用</th>
                                <th class="py-1 px-2 text-center bg-purple-100 dark:bg-purple-900/30" rowspan="2">残</th>
                            </tr>
                            <tr class="text-[10px]">
                                <th class="py-1 px-2 text-center bg-blue-100 dark:bg-blue-900/30 whitespace-nowrap border-r border-blue-200 dark:border-blue-800">昨年度</th>
                                <th class="py-1 px-2 text-center bg-blue-100 dark:bg-blue-900/30 whitespace-nowrap border-r border-blue-200 dark:border-blue-800">今年度</th>
                                <th class="py-1 px-2 text-center bg-blue-100 dark:bg-blue-900/30 whitespace-nowrap border-r border-r-gray-300 dark:border-r-gray-600">合計</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyReport as $month)
                            @php
                                $now = \Carbon\Carbon::now();
                                $isCurrentMonth = ($month['year'] == $now->year && $month['month'] == $now->month);
                            @endphp
                            <tr class="{{ $isCurrentMonth ? 'bg-yellow-50 dark:bg-yellow-900/10' : 'bg-white dark:bg-gray-800' }} border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="py-3 px-2 font-medium text-gray-900 dark:text-white whitespace-nowrap border-r border-gray-300 dark:border-gray-600">
                                    {{ $month['label'] }}
                                    @if($isCurrentMonth)
                                    <span class="text-xs text-yellow-600 dark:text-yellow-400">●</span>
                                    @endif
                                </td>
                                @php $paidCounted = $month['paid_used'] - $month['paid_future']; @endphp
                                <td class="py-3 px-2 text-center border-r border-blue-200 dark:border-blue-800 {{ $paidCounted > 0 ? 'text-blue-600 dark:text-blue-400 font-medium' : '' }}">
                                    @if($month['paid_future'] > 0)
                                        {{ number_format($paidCounted, 1) }} <span class="text-gray-400 dark:text-gray-500 font-normal text-[10px]">({{ number_format($month['paid_future'], 1) }})</span>
                                    @elseif($paidCounted > 0)
                                        {{ number_format($paidCounted, 1) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center text-xs text-gray-500 dark:text-gray-400 border-r border-blue-200 dark:border-blue-800">
                                    {{ $month['paid_prev_fy_remaining'] > 0 ? number_format($month['paid_prev_fy_remaining'], 1) : '-' }}
                                </td>
                                <td class="py-3 px-2 text-center text-xs text-gray-500 dark:text-gray-400 border-r border-blue-200 dark:border-blue-800">
                                    {{ number_format(round($month['paid_remaining'] - $month['paid_prev_fy_remaining'], 1), 1) }}
                                </td>
                                <td class="py-3 px-2 text-center font-medium text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">
                                    {{ number_format($month['paid_remaining'], 1) }}
                                </td>
                                @php $annualCounted = $month['annual_used'] - $month['annual_future']; @endphp
                                <td class="py-3 px-2 text-center border-r border-green-200 dark:border-green-800 {{ $annualCounted > 0 ? 'text-green-600 dark:text-green-400 font-medium' : '' }}">
                                    @if($month['annual_future'] > 0)
                                        {{ number_format($annualCounted, 1) }} <span class="text-gray-400 dark:text-gray-500 font-normal text-[10px]">({{ number_format($month['annual_future'], 1) }})</span>
                                    @elseif($annualCounted > 0)
                                        {{ number_format($annualCounted, 1) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600">
                                    {{ number_format($month['annual_remaining'], 1) }}
                                </td>
                                @php $compCounted = $month['comp_used'] - $month['comp_future']; @endphp
                                <td class="py-3 px-2 text-center border-r border-purple-200 dark:border-purple-800 {{ $compCounted > 0 ? 'text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                    @if($month['comp_future'] > 0)
                                        {{ number_format($compCounted, 1) }} <span class="text-gray-400 dark:text-gray-500 font-normal text-[10px]">({{ number_format($month['comp_future'], 1) }})</span>
                                    @elseif($compCounted > 0)
                                        {{ number_format($compCounted, 1) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center text-gray-700 dark:text-gray-300">
                                    {{ number_format($month['comp_remaining'], 1) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 使用履歴 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4" x-data="{ open: false }">
                <button @click="open = !open" class="w-full p-4 flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <i class="fas fa-history"></i>&nbsp;使用履歴
                        <span class="text-xs font-normal text-gray-400 ml-1">({{ count($usageHistory) }}件)</span>
                    </h3>
                    <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="overflow-x-auto border-t border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="py-3 px-4">日付</th>
                                <th scope="col" class="py-3 px-4">種別</th>
                                <th scope="col" class="py-3 px-4">日数</th>
                                <th scope="col" class="py-3 px-4">備考</th>
                                <th scope="col" class="py-3 px-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usageHistory as $usage)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="py-3 px-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $usage['usage_date'] }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($usage['leave_type'] === 'paid')
                                    <span class="text-xs font-medium px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">{{ $usage['type_label'] }}</span>
                                    @elseif($usage['leave_type'] === 'annual')
                                    <span class="text-xs font-medium px-2 py-0.5 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">{{ $usage['type_label'] }}</span>
                                    @elseif($usage['leave_type'] === 'expired_stock')
                                    <span class="text-xs font-medium px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300">{{ $usage['type_label'] }}</span>
                                    @else
                                    <span class="text-xs font-medium px-2 py-0.5 rounded bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">{{ $usage['type_label'] }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ number_format($usage['days'], 1) }}</td>
                                <td class="py-3 px-4">{{ $usage['note'] ?? '' }}</td>
                                <td class="py-3 px-4">
                                    <button class="delete-usage btn-red-g" data-id="{{ $usage['id'] }}">
                                        <i class="fas fa-trash"></i>&nbsp;削除
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr class="bg-white dark:bg-gray-800">
                                <td colspan="5" class="py-4 px-4 text-center text-gray-400">使用記録はありません</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 付与履歴 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4" x-data="{ open: false }">
                <button @click="open = !open" class="w-full p-4 flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <i class="fas fa-gift"></i>&nbsp;付与履歴
                        <span class="text-xs font-normal text-gray-400 ml-1">({{ count($grantHistory) }}件)</span>
                    </h3>
                    <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="overflow-x-auto border-t border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="py-3 px-4">種別</th>
                                <th scope="col" class="py-3 px-4">年度</th>
                                <th scope="col" class="py-3 px-4">日数</th>
                                <th scope="col" class="py-3 px-4">有効開始</th>
                                <th scope="col" class="py-3 px-4">有効期限</th>
                                <th scope="col" class="py-3 px-4">備考</th>
                                <th scope="col" class="py-3 px-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($grantHistory as $grant)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="py-3 px-4">
                                    @if($grant['leave_type'] === 'paid')
                                    <span class="text-xs font-medium px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">{{ $grant['type_label'] }}</span>
                                    @elseif($grant['leave_type'] === 'annual')
                                    <span class="text-xs font-medium px-2 py-0.5 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">{{ $grant['type_label'] }}</span>
                                    @else
                                    <span class="text-xs font-medium px-2 py-0.5 rounded bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">{{ $grant['type_label'] }}</span>
                                    @endif
                                    @if($grant['is_auto'])
                                    <span class="text-xs text-gray-400 ml-1">自動</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-900 dark:text-white">{{ $grant['fiscal_year'] }}</td>
                                <td class="py-3 px-4 font-medium text-gray-900 dark:text-white">{{ number_format($grant['grant_days'], 1) }}日</td>
                                <td class="py-3 px-4">{{ $grant['effective_date'] }}</td>
                                <td class="py-3 px-4">{{ $grant['expiry_date'] }}</td>
                                <td class="py-3 px-4">{{ $grant['note'] ?? '' }}</td>
                                <td class="py-3 px-4">
                                    <button class="delete-grant btn-red-g" data-id="{{ $grant['id'] }}">
                                        <i class="fas fa-trash"></i>&nbsp;削除
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr class="bg-white dark:bg-gray-800">
                                <td colspan="7" class="py-4 px-4 text-center text-gray-400">付与記録はありません</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- 休暇使用登録モーダル --}}
    <div id="modal-add-usage" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">休暇使用を登録</h3>
                <button id="close-usage-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('leave.usage.add') }}">
                @csrf
                <input type="hidden" name="fiscal_year" value="{{ $selectedFY }}">
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">種別</label>
                        <select name="leave_type" class="select-normal w-full" required>
                            <option value="paid">有休</option>
                            <option value="annual">年次休暇</option>
                            <option value="compensatory">代休</option>
                            @if($showExpiredStock)
                            <option value="expired_stock">失効累積</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">使用日</label>
                        <input type="date" name="usage_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">日数</label>
                        <select name="days" class="select-normal w-full" required>
                            <option value="1">1日</option>
                            <option value="0.5">0.5日（半日）</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">備考</label>
                        <input type="text" name="note" maxlength="100" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="任意">
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t dark:border-gray-700">
                    <button type="button" id="cancel-usage-modal" class="btn-alternative">キャンセル</button>
                    <button type="submit" class="btn-blue">登録</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 付与追加モーダル --}}
    <div id="modal-add-grant" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">付与を追加</h3>
                <button id="close-grant-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('leave.grant.add') }}">
                @csrf
                <input type="hidden" name="selected_fy" value="{{ $selectedFY }}">
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">種別</label>
                        <select id="grant-leave-type" name="leave_type" class="select-normal w-full" required>
                            <option value="paid">有休</option>
                            <option value="annual">年次休暇</option>
                            <option value="compensatory">代休</option>
                        </select>
                    </div>
                    <div id="grant-fiscal-year-group">
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">年度</label>
                        <select name="fiscal_year" class="select-normal w-full" required>
                            @foreach($fiscalYears as $fy)
                            <option value="{{ $fy }}" {{ $fy == $selectedFY ? 'selected' : '' }}>{{ $fy }}年度</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">有休・年次休暇の有効期限は年度に基づいて自動設定されます</p>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">付与日数</label>
                        <input type="number" name="grant_days" step="0.5" min="0.5" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">有効開始日</label>
                        <input type="date" name="effective_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">備考</label>
                        <input type="text" name="note" maxlength="100" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="任意">
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t dark:border-gray-700">
                    <button type="button" id="cancel-grant-modal" class="btn-alternative">キャンセル</button>
                    <button type="submit" class="btn-blue">追加</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
