<x-app-layout>
    <script type="module">
        function delete_holiday(num){
            return $.ajax({
                type : 'POST',
                url : '{{ route('settings.holiday_del') }}',
                data : { num : num },
                headers : {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
        }
        function load_template_preview(year){
            return $.ajax({
                type : 'GET',
                url : '{{ route('settings.holiday_template_preview') }}',
                data : { year : year },
            });
        }
        $(function () {
            {{-- 年セレクタ --}}
            $('#year-select').on('change', function () {
                var year = $(this).val();
                window.location.href = '{{ route('settings.holiday') }}?year=' + year;
            });

            {{-- アラート閉じる --}}
            $('#alert-btn').on('click', function (){
                $('#alert-div').hide();
            });

            {{-- 削除 --}}
            $('.delete_hol').on('click', function (){
                if (window.confirm('対象行の設定を削除します。よろしいですか？')){
                    var row = $(this).closest('tr');
                    var num = row.find('input.num').val();
                    delete_holiday(num).done(function (data) {
                        row.remove();
                        {{-- 件数を更新 --}}
                        var count = $('.result_table_body tr').length;
                        $('#holiday-count').text(count);
                        if (count === 0) {
                            $('.result_table_body').html('<tr class="bg-white dark:bg-gray-800"><td colspan="4" class="py-4 px-6 text-center text-gray-400">登録された祝祭日はありません</td></tr>');
                        }
                    }).fail(function (){
                        alert('削除に失敗しました');
                    });
                }
            });

            {{-- 追加モーダル --}}
            $('#btn-add-holiday').on('click', function () {
                $('#modal-add-holiday').removeClass('hidden');
            });
            $('#close-add-modal, #cancel-add-modal').on('click', function () {
                $('#modal-add-holiday').addClass('hidden');
            });

            {{-- テンプレートモーダル --}}
            $('#btn-template').on('click', function () {
                var year = $('#year-select').val();
                $('#template-loading').show();
                $('#template-table-body').empty();
                $('#template-count').text('');
                $('#btn-template-confirm').hide();
                $('#modal-template').removeClass('hidden');

                load_template_preview(year).done(function (data) {
                    $('#template-loading').hide();
                    if (data.length === 0) {
                        $('#template-table-body').html('<tr><td colspan="2" class="py-4 px-6 text-center text-gray-400">テンプレートデータがありません</td></tr>');
                        return;
                    }
                    $('#template-count').text(data.length + '件');
                    $.each(data, function(i, item){
                        $('#template-table-body').append(
                            '<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">' +
                            '<td class="py-2 px-4 text-gray-900 dark:text-white">' + item.date + '</td>' +
                            '<td class="py-2 px-4 text-gray-700 dark:text-gray-300">' + item.name + '</td>' +
                            '</tr>'
                        );
                    });
                    $('#btn-template-confirm').show();
                }).fail(function () {
                    $('#template-loading').hide();
                    $('#template-table-body').html('<tr><td colspan="2" class="py-4 px-6 text-center text-red-500">読み込みに失敗しました</td></tr>');
                });
            });
            $('#close-template-modal, #cancel-template-modal').on('click', function () {
                $('#modal-template').addClass('hidden');
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
            {{ __('Settings Holiday') }}
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

            {{-- 年セレクタ + アクションボタン --}}
            <div class="mb-4">
                <div class="flex flex-wrap items-end -mx-3">
                    <div class="w-full md:w-1/6 px-3 mb-3 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 dark:text-gray-400 text-xs font-bold mb-2" for="year-select">
                            表示年
                        </label>
                        <select id="year-select" class="select-normal w-full">
                            @foreach($year_list as $y)
                            <option value="{{ $y }}" {{ $y == $selected_year ? 'selected' : '' }}>{{ $y }}年</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 px-3 flex flex-wrap gap-2 justify-end self-end">
                        <button type="button" id="btn-add-holiday" class="btn-blue !mb-0">
                            <i class="fas fa-plus"></i>&nbsp;追加
                        </button>
                        @if($has_template)
                        <button type="button" id="btn-template" class="btn-alternative-green !mb-0">
                            <i class="fas fa-calendar-plus"></i>&nbsp;テンプレートから追加
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 一覧 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm dark:shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        <i class="fas fa-calendar-check"></i>&nbsp;{{ $selected_year }}年 祝祭日一覧
                        <span id="holiday-count" class="text-xs font-normal text-gray-400 ml-1">{{ count($result_list) }}</span>
                        <span class="text-xs font-normal text-gray-400">件</span>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="py-3 px-6">日付</th>
                            <th scope="col" class="py-3 px-6">祝祭日名</th>
                            <th scope="col" class="py-3 px-6">メモ</th>
                            <th scope="col" class="py-3 px-6"></th>
                        </tr>
                        </thead>
                        <tbody class="result_table_body">
                            @forelse($result_list as $result_item)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <input type="hidden" class="num" value="{{$result_item['num']}}">
                                <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{$result_item['date']}}
                                </th>
                                <td class="py-4 px-6">
                                    {{$result_item['name']}}
                                </td>
                                <td class="py-4 px-6">
                                    {{$result_item['note']}}
                                </td>
                                <td class="py-4 px-6">
                                    <button class="delete_hol btn_show btn-purple-to-blue-b group">
                                        <span class="btn-purple-to-blue-s">
                                            <i class="fas fa-trash"></i>&nbsp;削除
                                        </span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr class="bg-white dark:bg-gray-800">
                                <td colspan="4" class="py-4 px-6 text-center text-gray-400">登録された祝祭日はありません</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 祝祭日追加モーダル --}}
    <div id="modal-add-holiday" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">祝祭日を追加</h3>
                <button id="close-add-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('settings.holiday_add') }}">
                @csrf
                <input type="hidden" name="selected_year" value="{{ $selected_year }}">
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">年月日</label>
                        <input type="date" name="date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">祝祭日名</label>
                        <input type="text" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">メモ</label>
                        <input type="text" name="note" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="任意">
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t dark:border-gray-700">
                    <button type="button" id="cancel-add-modal" class="btn-alternative">キャンセル</button>
                    <button type="submit" class="btn-blue">追加</button>
                </div>
            </form>
        </div>
    </div>

    {{-- テンプレート確認モーダル --}}
    <div id="modal-template" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg flex flex-col" style="max-height: calc(100vh - 2rem);">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700 flex-shrink-0">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    テンプレートから追加
                    <span id="template-count" class="text-sm font-normal text-gray-400 ml-2"></span>
                </h3>
                <button id="close-template-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
            <div class="p-4 overflow-y-auto flex-1 min-h-0">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    以下の祝祭日が追加されます。確認の上、実行してください。
                    <br><span class="text-xs text-yellow-600 dark:text-yellow-400">※データは重複追加されます。必要に応じて削除を行ってください。</span>
                </p>
                <div id="template-loading" class="flex justify-center py-8" style="display:none;">
                    <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
                <div class="rounded border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0">
                            <tr>
                                <th scope="col" class="py-2 px-4">日付</th>
                                <th scope="col" class="py-2 px-4">祝祭日名</th>
                            </tr>
                        </thead>
                        <tbody id="template-table-body">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex justify-end gap-2 p-4 border-t dark:border-gray-700 flex-shrink-0">
                <button type="button" id="cancel-template-modal" class="btn-alternative">キャンセル</button>
                <form method="POST" action="{{ route('settings.holiday_template_add') }}" class="inline">
                    @csrf
                    <input type="hidden" name="year" value="{{ $selected_year }}">
                    <button type="submit" id="btn-template-confirm" class="btn-blue" style="display:none;">
                        <i class="fas fa-check"></i>&nbsp;追加する
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
