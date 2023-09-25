<x-app-layout>
    <script type="module">
        function edit_memo(edit_date,memo){
            return $.ajax({
                type : 'POST',
                url : '{{ route('memo_edit') }}',
                data : {
                    edit_date : edit_date,
                    memo : memo,
                },
                headers : {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
        }
        function create_copy_text(is_copy_work_time,is_copy_memo){
            var ret_text = '';
            var data_list = $('.result_table_body').find('tr').each(function (index){
                var tmp_text = '';
                if(is_copy_work_time){
                    tmp_text = tmp_text + $(this).find('td.s_datetime').text().replace('-','') + '\t';
                    tmp_text = tmp_text + $(this).find('td.e_datetime').text().replace('-','') + '\t';
                    tmp_text = tmp_text + $(this).find('td.work_time').text().replace('-','').replace(' H','');
                }else {
                    tmp_text = tmp_text + $(this).find('td.s_datetime').text().replace('-','') + '\t';
                    tmp_text = tmp_text + $(this).find('td.e_datetime').text().replace('-','');
                }
                if(is_copy_memo){
                    tmp_text = tmp_text + '\t' + $(this).find('.memo_show').text();
                }
                ret_text = ret_text + tmp_text + '\n';
            });

            return ret_text;
        }
        $(function () {
            {{-- リスナイベント --}}
            $('#alert-btn').on('click', function (){
                $('#alert-div').hide();
            });
            $('.edit_memo').on('click', function (){
                var parent_tr = $(this).parents('tr');
                parent_tr.children('td.memo').find('.memo_input').show();
                parent_tr.find('.btn_edit').show();
                parent_tr.children('td.memo').find('.memo_show').hide();
                parent_tr.find('.btn_show').hide();
                parent_tr.find('.btn_detail').hide();
                parent_tr.children('td.memo').find('.memo_input').focus();
            });
            $('.edit_submit').on('click', function (){
                {{--通信を行い、成功したら反映と切替処理を実施--}}
                var parent_tr = $(this).parents('tr');
                var date = parent_tr.find('.date_format').val();
                var new_memo = parent_tr.children('td.memo').find('.memo_input').val();
                edit_memo(date,new_memo).done(function (data) {
                    console.log(data['message']);
                    //表示切替処理
                    parent_tr.children('td.memo').find('.memo_show').text(new_memo);
                    parent_tr.children('td.memo').find('.memo_input').hide();
                    parent_tr.find('.btn_edit').hide();
                    parent_tr.children('td.memo').find('.memo_show').show();
                    parent_tr.find('.btn_show').show();
                    parent_tr.find('.btn_detail').show();
                }).fail(function (){
                    console.log('fail');
                });
            });
            $('.edit_cancel').on('click', function (){
                {{--labelの文字を取得してinputに復元し、切替処理を実施--}}
                var parent_tr = $(this).parents('tr');
                var org_memo = parent_tr.children('td.memo').find('.memo_show').text();
                parent_tr.children('td.memo').find('.memo_input').val(org_memo);
                //表示切替処理
                parent_tr.children('td.memo').find('.memo_input').hide();
                parent_tr.find('.btn_edit').hide();
                parent_tr.children('td.memo').find('.memo_show').show();
                parent_tr.find('.btn_show').show();
                parent_tr.find('.btn_detail').show();
            });
            $('.memo_input').keypress(function (e){
                if (e.keyCode == 13) { //Enter-更新処理
                    {{--通信を行い、成功したら反映と切替処理を実施--}}
                    var parent_tr = $(this).parents('tr');
                    var date = parent_tr.find('.date_format').val();
                    var new_memo = parent_tr.children('td.memo').find('.memo_input').val();
                    edit_memo(date,new_memo).done(function (data) {
                        console.log(data['message']);
                        //表示切替処理
                        parent_tr.children('td.memo').find('.memo_show').text(new_memo);
                        parent_tr.children('td.memo').find('.memo_input').hide();
                        parent_tr.find('.btn_edit').hide();
                        parent_tr.children('td.memo').find('.memo_show').show();
                        parent_tr.find('.btn_show').show();
                        parent_tr.find('.btn_detail').show();
                    }).fail(function (){
                        console.log('fail');
                    });
                }
            });
            $('.row_detail').on('click', function (){
                var parent_tr = $(this).parents('tr');
                var date = parent_tr.find('.date_format').val();
                $('#tgt_date').val(date);
                $('#detail_post').submit();
            });
            $('.copy').on('click', function (){
                var text = create_copy_text(true,false);
                //コピー処理
                var $textarea = $('#copy-area');
                $textarea.text(text);
                $textarea.show();
                $textarea.select();
                document.execCommand('copy');
                $textarea.hide();
                {{--navigator.clipboardはHTTPS環境でのみ動作--}}
                // navigator.clipboard.writeText('test');
                $('.copy_mes').show();
            });
            $('.copy_min').on('click', function (){
                var text = create_copy_text(false,false);
                //コピー処理
                var $textarea = $('#copy-area');
                $textarea.text(text);
                $textarea.show();
                $textarea.select();
                document.execCommand('copy');
                $textarea.hide();
                {{--navigator.clipboardはHTTPS環境でのみ動作--}}
                // navigator.clipboard.writeText('test');
                $('.copy_mes').show();
            });
            $('.copy_full').on('click', function (){
                var text = create_copy_text(true,true);
                //コピー処理
                var $textarea = $('#copy-area');
                $textarea.text(text);
                $textarea.show();
                $textarea.select();
                document.execCommand('copy');
                $textarea.hide();
                {{--navigator.clipboardはHTTPS環境でのみ動作--}}
                // navigator.clipboard.writeText('test');
                $('.copy_mes').show();
            });
        });
    </script>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-400 leading-tight">
            {{ __('Search') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm dark:shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-300">
                    {{-- 検索Formエリア --}}
                    <form method="post">
                        @csrf
                        <input type="hidden" name="selected_y" value="{{$selected_year}}">
                        <input type="hidden" name="selected_m" value="{{$selected_month}}">
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
                                <label class="block uppercase tracking-wide text-gray-700 dark:text-gray-400 text-xs font-bold mb-2" for="grid-year">
                                    年
                                </label>
                                {{ Form::select('year',$year_list,$selected_year,['class'=>'select-normal','id'=>'grid-year']) }}
                            </div>
                            <div class="w-full md:w-1/6 px-3">
                                <label class="block uppercase tracking-wide text-gray-700 dark:text-gray-400 text-xs font-bold mb-2" for="grid-month">
                                    月
                                </label>
                                {{ Form::select('month',$month_list,$selected_month,['class'=>'select-normal','id'=>'grid-month']) }}
                            </div>
                        </div>
                        <div class="flex flex-row-reverse">
                            <button type="submit" class="btn-blue" name="action" value="search"><i class="fas fa-search"></i>&nbsp;検索</button>
                            <button type="submit" class="btn-blue !mr-auto" name="action" value="next">翌月へ&nbsp;<i class="fas fa-angle-right"></i></button>
                            <button type="submit" class="btn-blue" name="action" value="back"><i class="fas fa-angle-left"></i>&nbsp;前月へ</button>
                        </div>
                        <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">
                        <div class="">
                            <textarea id="copy-area" style="display: none"></textarea>
                            <button type="button" class="copy btn-alternative"><i class="fas fa-copy"></i>&nbsp;コピー</button>
                            <button type="button" class="copy_min btn-alternative-green"><i class="fas fa-copy"></i>&nbsp;出退勤のみコピー</button>
                            <button type="button" class="copy_full btn-alternative-red"><i class="fas fa-copy"></i>&nbsp;メモ含めフルコピー</button>
                            <span class="copy_mes text-xs text-gray-700 dark:text-gray-400 hidden">クリップボードにコピーしました！</span>
                        </div>
                    </form>
                    {{-- 検索Formエリア --}}
                    {{-- 検索結果表示エリア --}}
                    <form method="post" action="{{route('detail')}}" id="detail_post">
                        @csrf
                        <input type="hidden" name="action" value="detail">
                        <input type="hidden" name="tgt_date" id="tgt_date" value="">
                    </form>
                    <div class="overflow-x-auto relative shadow-md dark:shadow-md dark:shadow-gray-600 sm:rounded-lg mt-4">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="py-3 px-6">
                                        日付
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        出勤時間
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        退勤時間
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        勤務時間
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
                                @if($result_item['is_today'] == 1){{--今日--}}
                                <tr class="bg-sky-50 border-b dark:bg-sky-800 dark:border-gray-700 hover:bg-sky-100 dark:hover:bg-sky-600">
                                @else
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                @endif
                                    @if($result_item['week'] == 0 || $result_item['holiday'] == 1){{--日曜 or 祝祭日--}}
                                    <th scope="row" class="py-4 px-6 font-medium text-red-600 whitespace-nowrap dark:text-red-400">
                                    @elseif($result_item['week'] == 6){{--土曜--}}
                                    <th scope="row" class="py-4 px-6 font-medium text-blue-600 whitespace-nowrap dark:text-blue-400">
                                    @else
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    @endif
                                        {{$result_item['date']}}
                                        <input type="hidden" class="date_format" value="{{$result_item['date_format']}}">
                                        @if($result_item['holiday_name'] != '')
                                            <br><span class="" style="font-size: .7rem;">{{$result_item['holiday_name']}}</span>
                                        @endif
                                    </th>
                                    @if($result_item['is_manual_s'] == '1')
                                    <td class="py-4 px-6 s_datetime">{{$result_item['s_datetime']}}<span class="font-bold text-black dark:text-white">*</span></td>
                                    @else
                                    <td class="py-4 px-6 s_datetime">{{$result_item['s_datetime']}}</td>
                                    @endif
                                    @if($result_item['is_manual_e'] == '1')
                                    <td class="py-4 px-6 e_datetime">{{$result_item['e_datetime']}}<span class="font-bold text-black dark:text-white">*</span></td>
                                    @else
                                    <td class="py-4 px-6 e_datetime">{{$result_item['e_datetime']}}</td>
                                    @endif
                                    <td class="py-4 px-6 work_time">{{$result_item['work_time']}}</td>
                                    <td class="memo py-4 px-6"><span class="memo_show">{{$result_item['memo']}}</span><input type="text" class="memo_input memo-input" style="display: none" value="{{$result_item['memo']}}"/></td>
                                    <td class="py-4 px-6">
                                        <button class="btn_show btn-purple-to-blue-b group">
                                            <span class="edit_memo btn-purple-to-blue-s">
                                                <i class="fas fa-edit text-sm"></i>&nbsp;メモ編集
                                            </span>
                                        </button>
                                        <button class="btn_detail btn-cyan-to-blue-b group">
                                            <span class="row_detail btn-cyan-to-blue-s">
                                                <i class="fas fa-info-circle text-sm"></i>&nbsp;詳細
                                            </span>
                                        </button>
                                        <span class="btn_edit hidden">
                                            <button type="button" class="edit_submit btn-green-g mr-2"><i class="fas fa-check"></i>&nbsp;確定</button>
                                            <button type="button" class="edit_cancel btn-red-g"><i class="fas fa-undo"></i>&nbsp;取消</button>
                                        </span>
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
