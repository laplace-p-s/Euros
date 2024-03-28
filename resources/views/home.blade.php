<x-app-layout>
    <script type="module">
        function setTime(){
            var now = new Date();
            var weekday = ['日', '月', '火', '水', '木', '金', '土'];
            var month = now.getMonth() + 1;
            var display_text_date = now.getFullYear() + "年 " + ('0' + month).slice(-2) + "月 " + ('0' + now.getDate()).slice(-2) + "日 (" + weekday[now.getDay()] + ")";
            var display_text_time = now.getHours() + ":" + ('0' + now.getMinutes()).slice(-2) + ":" + ('0' + now.getSeconds()).slice(-2);
            $('#date').html(display_text_date);
            $('#time').html(display_text_time);
        }
        function register_ajax(method_num){
            $.ajax({
                type : 'POST',
                url : '{{ route('register_rec') }}',
                data : { method : method_num },
                headers : {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            }).done(function (data) {
                $('.alert-1-text').text(data['message']);
            }).fail(function (){
                console.log('fail');
                $('.alert-1-text').text('登録処理が失敗しました。ページを再読み込みの上、再度お試し下さい。');
            }).always(function (){
                renewal_info();
            });
            $('#alert-div').show();
        }
        function renewal_info(){
            $.ajax({
                type : 'POST',
                url : '{{ route('renewal_info') }}',
                headers : {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            }).done(function (data) {
                $('#w_time').text(data['w_time']);
                $('#s_time').text(data['s_time']);
                $('#e_time').text(data['e_time']);
            }).fail(function (){
                console.log('fail renewal');
                $('#w_time').text('? H');
                $('#s_time').text('??:??:??');
                $('#e_time').text('??:??:??');
            });
        }
        setTime();{{-- 初回実行分 --}}
        setInterval(setTime, 100);{{-- その後は0.1秒に1回実行させる --}}
        setInterval(renewal_info, 1000*60*1);{{-- 1秒*60*1(1分)に1回実行させる --}}
        $(function () {
            {{-- リスナイベント --}}
            $('#alert-btn').on('click', function (){
                $('#alert-div').hide();
            });
            $('#action-attend').on('click', function (){
                register_ajax(1);
            });
            $('#action-leave').on('click', function (){
                register_ajax(2);
            });
        });
    </script>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-400 leading-tight">
            {{ __('Home') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Infoエリア --}}
            <div id="alert-div" class="hidden">
                <div class="alert-1-div" role="alert">
                    <svg aria-hidden="true" class="flex-shrink-0 w-5 h-5 text-blue-700 dark:text-blue-800" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    <span class="sr-only">Info</span>
                    <div class="alert-1-text">
                        登録が完了しました。
                    </div>
                    <button id="alert-btn" type="button" class="alert-1-close" aria-label="Close">
                        <span class="sr-only">Close</span>
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
            </div>
            {{-- Infoエリア --}}
            {{-- mainエリア --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm dark:shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-center items-center mb-2">
                        <span id="date" class="text-2xl dark:text-gray-100"></span>
                    </div>
                    <div class="flex justify-center items-center mb-2">
                        <span id="time" class="text-2xl dark:text-gray-100"></span>
                    </div>
                    <hr class="w-48 h-1 mx-auto my-3 bg-gray-100 border-0 rounded md:my-10 dark:bg-gray-700">
                    {{--現在勤務時間表示--}}
                    <div class="flex justify-center items-center">
                        <span id="w_time" class="w-32 flex justify-center items-center dark:text-gray-100">{{$today_info['w_time']}}</span>
                    </div>
                    {{--現在勤務時間表示--}}
                    {{--当日日付記録表示--}}
                    <div class="flex justify-center items-center mb-2">
                        <span id="s_time" class="w-32 flex justify-center items-center dark:text-gray-100">{{$today_info['s_time']}}</span>
                        <span id="e_time" class="w-32 flex justify-center items-center dark:text-gray-100 ml-7">{{$today_info['e_time']}}</span>
                    </div>
                    {{--当日日付記録表示--}}
                    <div class="flex justify-center items-center">
                        <button type="button" id="action-attend" name="action-attend" value="attend" class="home-btn">
                            <p class="fs-4 fw-bold">出勤</p>
                            <img src="image/attend_man.png" width="100px" height="100px">
                        </button>
                        <button type="button" id="action-leave" name="action-leave" value="leave" class="home-btn ml-7">
                            <p class="fs-4 fw-bold">退勤</p>
                            <img src="image/leave_man.png" width="100px" height="100px">
                        </button>
                    </div>
                </div>
            </div>
            {{-- mainエリア --}}
            {{-- summaryエリア --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm dark:shadow-sm sm:rounded-lg mt-3 sm:w-1/2">
                <div class="p-6 text-gray-900">
                    <span class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $summary_info['now_disp'] }} サマリ</span><br>
                    <span class="text-xs text-gray-900 whitespace-nowrap dark:text-white">{{ $summary_info['now_announce'] }} 現在</span><br>
                    <span class="text-blue-600 underline"><a href="{{ route('home', ['summary_y' => $summary_info['last_month_y'], 'summary_m'=>$summary_info['last_month_m']]) }}"><i class="fas fa-arrow-left"></i>&nbsp;前月</a></span>
                    @if($summary_info['is_display_next'])
                    <span class="text-blue-600 underline"><a href="{{ route('home', ['summary_y' => $summary_info['next_month_y'], 'summary_m'=>$summary_info['next_month_m']]) }}">翌月&nbsp;<i class="fas fa-arrow-right"></i></a></span>
                    @endif
                </div>
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <tbody>
                            <tr class="bg-white dark:bg-gray-800">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">勤務時間合計</th>
                                <td class="px-6 py-4">{{ $summary_info['work_time_sum'] }}</td>
                            </tr>
                            <tr class="bg-white dark:bg-gray-800">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">実働日数</th>
                                <td class="px-6 py-4">{{ $summary_info['work_time_day'] }}</td>
                            </tr>
                            <tr class="bg-white dark:bg-gray-800">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">休日実働日数</th>
                                <td class="px-6 py-4">{{ $summary_info['work_time_day_h'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- summaryエリア --}}
        </div>
    </div>
</x-app-layout>
