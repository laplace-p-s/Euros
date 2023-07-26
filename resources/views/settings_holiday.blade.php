<x-app-layout>
    <script type="module">
        function delete_holiday(num){
            return $.ajax({
                type : 'POST',
                url : '{{ route('settings.holiday_del') }}',
                data : {
                    num : num,
                },
                headers : {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
        }
        $(function () {
            {{-- リスナイベント --}}
            $('.delete_hol').on('click', function (){
                var msg = '対象行の設定を削除します。よろしいですか？';
                if (window.confirm(msg)){
                    var row = $(this).parents('.row_top');
                    var num = row.children('input.num').val();
                    delete_holiday(num).done(function (data) {
                        console.log(data['message']);
                        //表示切替処理
                        row.remove();
                    }).fail(function (){
                        console.log('fail');
                    });
                }
            });
            $('#submit_t_add').on('click', function (){
                var msg = '現在年の国民の休日を一斉追加します。よろしいですか？\n※データは重複追加されます。必要に応じて削除を行ってください。';
                if (window.confirm(msg)){
                    //そのままPOST
                }else {
                    //POSTを止める
                    $('form').submit(false);
                }
            });
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
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm dark:shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="post" name="add">
                        @csrf
                        <div class="mb-3">
                            <label for="date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">年月日</label>
                            <input type="date" id="date" name="date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="yyyy/MM/dd" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">祝祭日名</label>
                            <input type="text" id="name" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>
                        <div class="mb-6">
                            <label for="note" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">メモ</label>
                            <input type="text" id="note" name="note" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>
                        <button type="submit" id="submit_add" name="action" value="add" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">追加</button>
                    </form>
                    <form method="post" name="t_add" class="mt-2">
                        @csrf
                        <button type="submit" id="submit_t_add" name="action" value="template_add" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 ml-auto">今年の休日テンプレートから追加</button>
                    </form>
                    <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">
                    {{-- 検索結果表示エリア --}}
                    <div class="overflow-x-auto relative shadow-md dark:shadow-md dark:shadow-gray-600 sm:rounded-lg mt-4">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="py-3 px-6">
                                    日付
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    祝祭日名
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    メモ
                                </th>
                                <th scope="col" class="py-3 px-6">

                                </th>
                            </tr>
                            </thead>
                            <tbody class="result_table_body">
                                @foreach($result_list as $result_item)
                                <tr class="row_top bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <input type="hidden" class="num" value="{{$result_item['num']}}">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{$result_item['date']}}
                                    </th>
                                    <td class="memo py-4 px-6">
                                        {{$result_item['name']}}
                                    </td>
                                    <td class="memo py-4 px-6">
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{--検索結果表示エリア--}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
