<x-app-layout>
    <script type="module">
        $(function () {
            {{-- リスナイベント --}}
            $('.btn_del').on('click', function (){
                var msg = '対象行の記録を削除します。よろしいですか？';
                if (window.confirm(msg)){
                    var parent_tr = $(this).parents('tr');
                    var num = parent_tr.find('.num').val();
                    $('#del_num').val(num);
                    $('#del_form').submit();
                }
            });
            $('.add_modal_btn').on('click', function (){
                $('#add_modal_bg').show();
                $('#add_modal').show();
            });
            $('#add_modal_bg').on('click', function (){
                $('#add_modal_bg').hide();
                $('#add_modal').hide();
            });
        });
    </script>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-400 leading-tight">
            {{ __('Detail') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Infoエリア --}}
            <form method="post" id="del_form">
                @csrf
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="num" id="del_num" value="">
                <input type="hidden" name="tgt_date" value="{{$tgt_date}}">
            </form>
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
                    {{--テーブル表示エリア--}}
                    <div class="relative overflow-x-auto">
                        <table class="w-1/2 text-sm text-left text-gray-500 dark:text-gray-400">
                            <tbody>
                                <tr class="bg-white dark:bg-gray-800">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">日付</th>
                                    <td class="px-6 py-4">{{$detail_data['date']}}</td>
                                </tr>
                                <tr class="bg-white dark:bg-gray-800">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">出勤時間</th>
                                    <td class="px-6 py-4">{{$detail_data['s_datetime']}}</td>
                                </tr>
                                <tr class="bg-white dark:bg-gray-800">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">退勤時間</th>
                                    <td class="px-6 py-4">{{$detail_data['e_datetime']}}</td>
                                </tr>
                                <tr class="bg-white dark:bg-gray-800">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">勤務時間</th>
                                    <td class="px-6 py-4">{{$detail_data['work_time']}}</td>
                                </tr>
                                <tr class="bg-white dark:bg-gray-800">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">メモ</th>
                                    <td class="px-6 py-4">{{$detail_data['memo']}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{--テーブル表示エリア--}}
                    <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">
                    {{--ボタン表示エリア--}}
                    <button type="button" class="btn-purple-to-blue add_modal_btn">
                        <i class="fas fa-plus"></i>&nbsp;記録の手動追加
                    </button>
                    {{--ボタン表示エリア--}}
                    {{-- 検索結果表示エリア --}}
                    <div class="flex justify-evenly mt-4">
                        <div class="overflow-x-auto w-auto max-w-1/2">
                            {{--table1--}}
                            <span class="font-medium text-gray-900 whitespace-nowrap dark:text-white">出勤記録</span>
                            <table class="text-sm text-left text-gray-500 dark:text-gray-400 shadow-md dark:shadow-md dark:shadow-gray-600 sm:rounded-lg">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">日付</th>
                                        <th scope="col" class="py-3 px-6"></th>
                                    </tr>
                                </thead>
                                <tbody class="result_table_body">
                                    @if($detail_data['attend_count'] == 0)
                                        <tr class="bg-white dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td colspan="2" class="py-4 px-6">no data.</td>
                                        </tr>
                                    @else
                                    @foreach($records_attend as $attends)
                                    <tr class="row_top bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <input type="hidden" class="num" value="{{$attends->id}}">
                                        @if($loop->iteration == $detail_data['attend_count'])
                                        <th scope="row" class="py-4 px-6 font-bold text-gray-900 whitespace-nowrap dark:text-white">
                                        @else
                                        <th scope="row" class="py-4 px-6 font-thin text-gray-900 whitespace-nowrap dark:text-white">
                                        @endif
                                            {{$attends->record_date}}
                                            @if($attends->is_manual == '1')
                                                <span class="font-bold text-black dark:text-white">*</span>
                                            @endif
                                        </th>
                                        <td class="py-4 px-6">
                                            <button class="btn_show btn-purple-to-blue-b group">
                                                <span class="btn_del btn-purple-to-blue-s">
                                                    <i class="fas fa-trash"></i>&nbsp;削除
                                                </span>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                            {{--table1--}}
                        </div>
                        <div class="overflow-x-auto w-auto max-w-1/2">
                            {{--table2--}}
                            <span class="font-medium text-gray-900 whitespace-nowrap dark:text-white">退勤記録</span>
                            <table class="text-sm text-left text-gray-500 dark:text-gray-400 shadow-md dark:shadow-md dark:shadow-gray-600 sm:rounded-lg">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">日付</th>
                                        <th scope="col" class="py-3 px-6"></th>
                                    </tr>
                                </thead>
                                <tbody class="result_table_body">
                                    @if($detail_data['leave_count'] == 0)
                                    <tr class="bg-white dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td colspan="2" class="py-4 px-6">no data.</td>
                                    </tr>
                                    @else
                                    @foreach($records_leave as $leaves)
                                    <tr class="row_top bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <input type="hidden" class="num" value="{{$leaves->id}}">
                                        @if($loop->iteration == $detail_data['leave_count'])
                                        <th scope="row" class="py-4 px-6 font-bold text-gray-900 whitespace-nowrap dark:text-white">
                                        @else
                                        <th scope="row" class="py-4 px-6 font-thin text-gray-900 whitespace-nowrap dark:text-white">
                                        @endif
                                            {{$leaves->record_date}}
                                            @if($leaves->is_manual == '1')
                                                <span class="font-bold text-black dark:text-white">*</span>
                                            @endif
                                        </th>
                                        <td class="py-4 px-6">
                                            <button class="btn_show btn-purple-to-blue-b group">
                                                <span class="btn_del btn-purple-to-blue-s">
                                                    <i class="fas fa-trash"></i>&nbsp;削除
                                                </span>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                            {{--table2--}}
                        </div>
                    </div>
                    {{--検索結果表示エリア--}}
                </div>
            </div>
        </div>
    </div>
    {{-- Modal area --}}
    <div id="add_modal_bg" tabindex="-1" class="fixed bg-gray-900 opacity-30 w-screen h-screen z-10 top-0 left-0 right-0 hidden"></div>
    {{-- Modal area --}}
    {{-- Modal Contents --}}
    <div id="add_modal" class="fixed z-20 top-0 left-0 right-0 mx-auto mt-32 w-3/4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm dark:shadow-sm sm:rounded-lg hidden">
        <div class="p-6 text-gray-900">
            <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">記録の手動追加</h3>
            <form method="post" action="{{route('detail.add_record')}}">
                @csrf
                {{-- 区分 --}}
                <label class="block uppercase tracking-wide text-gray-700 dark:text-gray-400 text-xs font-bold mb-2" for="grid-year">区分</label>
                <div class="flex items-center mb-4">
                    <input checked id="record-method-1" type="radio" value="1" name="record-method" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="record-method-1" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">出勤</label>
                </div>
                <div class="flex items-center">
                    <input id="record-method-2" type="radio" value="2" name="record-method" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="record-method-2" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">退勤</label>
                </div>
                {{-- 日付 --}}
                <label class="block uppercase tracking-wide text-gray-700 dark:text-gray-400 text-xs font-bold mb-1 mt-4" for="grid-year">日付</label>
                <div>
                    <label class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{$detail_data['date']}}</label>
                    <input type="hidden" name="record-date" value="{{$detail_data['date_orig']}}">
                </div>
                {{-- 時刻 --}}
                <label class="block uppercase tracking-wide text-gray-700 dark:text-gray-400 text-xs font-bold mb-2 mt-4" for="grid-year">時刻</label>
                <div>
                    <input type="time" name="record-time" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 w-1/6" placeholder="">
                </div>
                <div class="flex flex-row-reverse">
                    <button type="submit" class="btn-blue ml-auto" name="action" value="add_submit">登録</button>
                </div>
            </form>
        </div>
    </div>
    {{-- Modal Contents --}}
</x-app-layout>
