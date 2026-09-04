<x-app-layout>
    <script type="module">
        function delete_record(id) {
            return $.ajax({
                type: 'POST',
                url: '{{ route('transit.record.delete') }}',
                data: { record_id: id },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
        }
        function create_copy_text() {
            var ret_text = '';
            $('.transit_table_body').find('tr.record-row').each(function () {
                var tmp_text = '';
                tmp_text = tmp_text + $(this).find('td.tsv_date').data('date') + '\t';
                tmp_text = tmp_text + $(this).find('td.tsv_label').text().trim() + '\t';
                tmp_text = tmp_text + $(this).find('td.tsv_route').text().trim() + '\t';
                tmp_text = tmp_text + $(this).find('td.tsv_amount').data('amount') + '\t';
                tmp_text = tmp_text + $(this).find('td.tsv_note').text().trim();
                ret_text = ret_text + tmp_text + '\n';
            });
            return ret_text;
        }
        function create_seq_copy_text() {
            var ret_text = '';
            var seq = 0;
            $('.transit_table_body').find('tr.record-row').each(function () {
                seq = seq + 1;
                var tmp_text = '';
                tmp_text = tmp_text + $(this).find('td.tsv_date').data('date') + '\t';
                tmp_text = tmp_text + seq + '\t';
                tmp_text = tmp_text + $(this).find('td.tsv_label').text().trim();
                ret_text = ret_text + tmp_text + '\n';
            });
            return ret_text;
        }
        function copy_to_clipboard(text) {
            var $textarea = $('#copy-area');
            $textarea.text(text);
            $textarea.show();
            $textarea.select();
            document.execCommand('copy');
            $textarea.hide();
            {{-- navigator.clipboardはHTTPS環境でのみ動作 --}}
            $('.copy_mes').show();
        }
        function apply_destination(select) {
            var opt = $(select).find('option:selected');
            $('#record-route').val(opt.data('route'));
            $('#record-amount').val(opt.data('amount'));
        }
        function reset_record_modal() {
            $('#record-modal-title').text('交通費を登録');
            $('#record-use-date').val('{{ $defaultDate }}');
            $('#record-destination-select').prop('selectedIndex', 0);
            apply_destination($('#record-destination-select'));
            $('#record-note').val('');
        }
        $(function () {
            {{-- アラート閉じる --}}
            $('#alert-btn').on('click', function () {
                $('#alert-div').hide();
            });

            {{-- 月セレクタ --}}
            $('#month-select').on('change', function () {
                window.location.href = '{{ route('transit') }}?month=' + $(this).val();
            });
            $('#month-back').on('click', function () {
                window.location.href = '{{ route('transit') }}?month={{ $selectedMonthValue }}&action=back';
            });
            $('#month-next').on('click', function () {
                window.location.href = '{{ route('transit') }}?month={{ $selectedMonthValue }}&action=next';
            });

            {{-- 明細削除 --}}
            $('.delete-record').on('click', function () {
                if (window.confirm('この交通費記録を削除します。よろしいですか？')) {
                    var id = $(this).data('id');
                    delete_record(id).done(function () {
                        location.reload();
                    }).fail(function () {
                        alert('削除に失敗しました');
                    });
                }
            });

            {{-- 明細登録モーダル --}}
            $('#btn-add-record').on('click', function () {
                reset_record_modal();
                $('#modal-add-record').removeClass('hidden');
            });

            {{-- 明細の複製（登録モーダルに既存の値を呼び出す） --}}
            $('.duplicate-record').on('click', function () {
                var destinationId = String($(this).data('destination-id') || '');
                $('#record-modal-title').text('交通費を登録（複製）');
                $('#record-use-date').val($(this).data('date'));
                {{-- 行き先が削除済みの場合は選択を変更しない --}}
                if (destinationId !== '' && $('#record-destination-select option[value="' + destinationId + '"]').length) {
                    $('#record-destination-select').val(destinationId);
                }
                $('#record-route').val($(this).data('route'));
                $('#record-amount').val($(this).data('amount'));
                $('#record-note').val($(this).data('note'));
                $('#modal-add-record').removeClass('hidden');
            });
            $('#record-destination-select').on('change', function () {
                apply_destination(this);
            });
            $('#close-record-modal, #cancel-record-modal').on('click', function () {
                $('#modal-add-record').addClass('hidden');
            });

            {{-- TSVコピー --}}
            $('.copy').on('click', function () {
                copy_to_clipboard(create_copy_text());
            });
            $('.copy_seq').on('click', function () {
                copy_to_clipboard(create_seq_copy_text());
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

            {{-- 月セレクタ + アクションボタン --}}
            <div class="mb-4">
                <div class="flex flex-wrap items-end -mx-3">
                    <div class="w-full md:w-auto px-3 mb-3 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 dark:text-gray-400 text-xs font-bold mb-2" for="month-select">
                            対象月
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" id="month-back" class="btn-alternative !mr-0 !mb-0 !py-2 !px-3" title="前月">
                                <i class="ti ti-chevron-left"></i>
                            </button>
                            <select id="month-select" class="select-normal">
                                @foreach($monthList as $month)
                                <option value="{{ $month }}" {{ $month == $selectedMonthValue ? 'selected' : '' }}>{{ str_replace('-', '年', $month) }}月</option>
                                @endforeach
                            </select>
                            <button type="button" id="month-next" class="btn-alternative !mr-0 !mb-0 !py-2 !px-3" title="翌月">
                                <i class="ti ti-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 px-3 flex flex-wrap gap-3 items-end justify-end self-end">
                        <div class="mb-0.5 mr-auto md:mr-0 text-right">
                            <span class="text-xs text-gray-700 dark:text-gray-400">当月合計</span>
                            <span class="ml-1 text-xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($total) }}</span><span class="text-xs text-gray-500 dark:text-gray-400"> 円</span>
                            <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">({{ count($records) }}件)</span>
                        </div>
                        @if(count($destinations) > 0)
                        <button type="button" id="btn-add-record" class="btn-blue !mr-0 !mb-0">
                            <i class="ti ti-plus"></i>&nbsp;交通費を登録
                        </button>
                        @else
                        <button type="button" class="btn-disabled !mr-0 !mb-0" disabled title="先に行き先を登録してください">
                            <i class="ti ti-plus"></i>&nbsp;交通費を登録
                        </button>
                        @endif
                        <button type="button" onclick="location.href='{{ route('transit.destination') }}'" class="btn-alternative-green !mr-0 !mb-0">
                            <i class="ti ti-map-pin"></i>&nbsp;行き先の管理
                        </button>
                    </div>
                </div>
            </div>

            @if(count($destinations) == 0)
            <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg">
                <div class="flex items-start">
                    <i class="ti ti-alert-triangle flex-shrink-0 text-xl text-yellow-600 dark:text-yellow-400 mt-0.5"></i>
                    <div class="ml-3 text-sm text-yellow-700 dark:text-yellow-400">
                        <p><a href="{{ route('transit.destination') }}" class="underline font-medium">行き先の管理</a>から行き先を登録すると、交通費を登録できるようになります。</p>
                        <p class="mt-1 text-xs">※ 往復する場合は「自宅→本社」「本社→自宅」のように行きと帰りを別々に登録してください</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- 明細一覧 --}}
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <i class="ti ti-list"></i>&nbsp;{{ $selectedMonthLabel }}の明細
                    <span class="text-xs font-normal text-gray-400 ml-1">({{ count($records) }}件)</span>
                </h3>
                <div class="flex items-center">
                    <textarea id="copy-area" style="display: none"></textarea>
                    <span class="copy_mes text-xs text-gray-700 dark:text-gray-400 hidden mr-2">クリップボードにコピーしました！</span>
                    <button type="button" class="copy btn-alternative !mr-2 !mb-0" title="日付・行き先・経路・金額・備考をコピー"><i class="ti ti-copy"></i>&nbsp;コピー</button>
                    <button type="button" class="copy_seq btn-alternative-green !mr-0 !mb-0" title="日付・連番・行き先をコピー"><i class="ti ti-list-numbers"></i>&nbsp;連番コピー</button>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4 border border-gray-300 dark:border-gray-500">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300 border-b border-gray-300 dark:border-gray-500">
                            <tr>
                                <th scope="col" class="py-3 px-4">日付</th>
                                <th scope="col" class="py-3 px-4">行き先</th>
                                <th scope="col" class="py-3 px-4">経路</th>
                                <th scope="col" class="py-3 px-4 text-right">金額</th>
                                <th scope="col" class="py-3 px-4">備考</th>
                                <th scope="col" class="py-3 px-4"></th>
                            </tr>
                        </thead>
                        <tbody class="transit_table_body">
                            @forelse($records as $record)
                            <tr class="record-row bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                                @if($record['week'] == 0)
                                <td class="tsv_date py-3 px-4 font-medium text-red-600 dark:text-red-400 whitespace-nowrap" data-date="{{ str_replace('-', '/', $record['use_date_raw']) }}">
                                @elseif($record['week'] == 6)
                                <td class="tsv_date py-3 px-4 font-medium text-blue-600 dark:text-blue-400 whitespace-nowrap" data-date="{{ str_replace('-', '/', $record['use_date_raw']) }}">
                                @else
                                <td class="tsv_date py-3 px-4 font-medium text-gray-900 dark:text-white whitespace-nowrap" data-date="{{ str_replace('-', '/', $record['use_date_raw']) }}">
                                @endif
                                    {{ $record['use_date'] }}
                                </td>
                                <td class="tsv_label py-3 px-4">{{ $record['label'] }}</td>
                                <td class="tsv_route py-3 px-4 text-xs">{{ $record['route'] ?? '' }}</td>
                                <td class="tsv_amount py-3 px-4 text-right font-medium text-gray-900 dark:text-white whitespace-nowrap" data-amount="{{ $record['amount'] }}">{{ number_format($record['amount']) }}円</td>
                                <td class="tsv_note py-3 px-4">{{ $record['note'] ?? '' }}</td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <button class="duplicate-record btn-green-g mr-2"
                                        data-date="{{ $record['use_date_raw'] }}"
                                        data-destination-id="{{ $record['destination_id'] }}"
                                        data-route="{{ $record['route'] }}"
                                        data-amount="{{ $record['amount'] }}"
                                        data-note="{{ $record['note'] }}">
                                        <i class="ti ti-copy"></i>&nbsp;複製
                                    </button>
                                    <button class="delete-record btn-red-g" data-id="{{ $record['id'] }}">
                                        <i class="ti ti-trash"></i>&nbsp;削除
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr class="bg-white dark:bg-gray-800">
                                <td colspan="6" class="py-4 px-4 text-center text-gray-400">この月の交通費は登録されていません</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($records) > 0)
                        <tfoot>
                            <tr class="bg-gray-100 dark:bg-gray-700 font-semibold text-gray-900 dark:text-white border-t border-gray-300 dark:border-gray-500">
                                <td class="py-3 px-4" colspan="3">合計</td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">{{ number_format($total) }}円</td>
                                <td class="py-3 px-4" colspan="2"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- 交通費登録モーダル --}}
    <div id="modal-add-record" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h3 id="record-modal-title" class="text-lg font-semibold text-gray-900 dark:text-white">交通費を登録</h3>
                <button id="close-record-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="ti ti-x text-xl"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('transit.record.add') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $selectedMonthValue }}">
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">利用日</label>
                        <input type="date" id="record-use-date" name="use_date" value="{{ $defaultDate }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">行き先</label>
                        <select id="record-destination-select" name="destination_id" class="select-normal w-full" required>
                            @foreach($destinations as $destination)
                            <option value="{{ $destination->id }}" data-route="{{ $destination->route }}" data-amount="{{ $destination->amount }}">{{ $destination->label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">往復した場合は行きと帰りを1件ずつ登録してください</p>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">経路</label>
                        <input type="text" id="record-route" name="route" maxlength="100" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="例：○○→△△" value="{{ optional($destinations->first())->route }}" required>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">行き先の登録内容が入ります。今回だけ変更したい場合はここで修正できます</p>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">金額（円）</label>
                        <input type="number" id="record-amount" name="amount" value="{{ optional($destinations->first())->amount }}" step="1" min="0" max="1000000" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">備考</label>
                        <input type="text" id="record-note" name="note" maxlength="100" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="任意">
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t dark:border-gray-700">
                    <button type="button" id="cancel-record-modal" class="btn-alternative !mr-0 !mb-0">キャンセル</button>
                    <button type="submit" class="btn-blue !mr-0 !mb-0">登録</button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
