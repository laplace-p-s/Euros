<x-app-layout>
    <script type="module">
        $(function () {
            {{-- リスナイベント --}}
        });
    </script>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-400 leading-tight">
            {{ __('Paid Leave') }}
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
            {{-- mainエリア --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm dark:shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 dark:text-gray-400 text-xs font-bold mb-2" for="grid-year">
                            残り休日数
                        </label>
                    </div>
                    <!---->
                    <div class="overflow-x-auto relative shadow-md dark:shadow-md dark:shadow-gray-600 sm:rounded-lg mt-4">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="py-3 px-6">残り有給日数</th>
                                    <th scope="col" class="py-3 px-6">今年度消える有給日数</th>
                                    <th scope="col" class="py-3 px-6">来年度消える有給日数</th>
                                    <th scope="col" class="py-3 px-6">残り代休日数</th>
                                    <th scope="col" class="py-3 px-6">残り年度休暇日数</th>
                                </tr>
                            </thead>
                            <tbody class="result_table_body">
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $paid_leave }}</td>
                                    <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $sub_yearly_paid_leave }}</td>
                                    <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $yearly_paid_leave }}</td>
                                    <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $compensatory_leave }}</td>
                                    <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $annual_leave }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!---->
                    <div class="p-6 text-gray-900">
                        <a href="{{ route('paid_leave') }}">
                            <button type="button" class="btn-blue"><i class="fas fa-cat"></i>&nbsp;{{ __('Paid Leave') }}</button>
                        </a>
                        <a href="{{ route('paid_leave') }}">
                            <button type="button" class="btn-blue"><i class="fas fa-cat"></i>&nbsp;{{ __('Paid Leave') }}</button>
                        </a>
                    </div>
                    <!---->
                    <div class="overflow-x-auto relative shadow-md dark:shadow-md dark:shadow-gray-600 sm:rounded-lg mt-4">
                        <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
                            <label class="block uppercase tracking-wide text-gray-700 dark:text-gray-400 text-xs font-bold mb-2" for="grid-year">
                                ○○年の取得履歴
                            </label>
                        </div>
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="py-3 px-6">
                                    年/月
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    使用有給数
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    使用代休数
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    使用年度休暇数
                                </th>
                                <th scope="col" class="py-3 px-6">
                                    カラム５
                                </th>
                            </tr>
                            </thead>
                            <tbody class="result_table_body">
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">-</th>
                                    <td class="py-4 px-6 s_datetime">-</td>
                                    <td class="py-4 px-6 e_datetime">-</td>
                                    <td class="py-4 px-6 work_time">-</td>
                                    <td class="memo py-4 px-6">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!---->
                </div>
            </div>
            {{-- mainエリア --}}
        </div>
    </div>
</x-app-layout>
