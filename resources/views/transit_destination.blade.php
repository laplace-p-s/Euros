<x-app-layout>
    <script type="module">
        function delete_destination(id) {
            return $.ajax({
                type: 'POST',
                url: '{{ route('transit.destination.delete') }}',
                data: { destination_id: id },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
        }
        function move_destination(id, direction) {
            return $.ajax({
                type: 'POST',
                url: '{{ route('transit.destination.move') }}',
                data: { destination_id: id, direction: direction },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
        }
        function pin_destination(id) {
            return $.ajax({
                type: 'POST',
                url: '{{ route('transit.destination.pin') }}',
                data: { destination_id: id },
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

            {{-- 行き先削除 --}}
            $('.delete-destination').on('click', function () {
                if (window.confirm('この行き先を削除します。登録済みの明細は残ります。よろしいですか？')) {
                    var id = $(this).data('id');
                    delete_destination(id).done(function () {
                        location.reload();
                    }).fail(function () {
                        alert('削除に失敗しました');
                    });
                }
            });

            {{-- 並び替え --}}
            $('.move-destination').on('click', function () {
                var $btn = $(this);
                $btn.prop('disabled', true);
                move_destination($btn.data('id'), $btn.data('direction')).done(function () {
                    location.reload();
                }).fail(function () {
                    $btn.prop('disabled', false);
                    alert('並び替えに失敗しました');
                });
            });

            {{-- ピン留め切り替え --}}
            $('.pin-destination').on('click', function () {
                var $btn = $(this);
                $btn.prop('disabled', true);
                pin_destination($btn.data('id')).done(function (res) {
                    var pinned = res.is_pinned;
                    $btn.toggleClass('btn-pin-on', pinned).toggleClass('btn-pin-off', !pinned);
                    $btn.find('i').toggleClass('ti-pinned', pinned).toggleClass('ti-pin', !pinned);
                    $btn.attr('title', pinned ? 'ピン留めを解除する' : 'クイック登録にピン留めする');
                    $btn.closest('tr').toggleClass('row-pinned', pinned);
                    $btn.prop('disabled', false);
                }).fail(function () {
                    $btn.prop('disabled', false);
                    alert('ピン留めの変更に失敗しました');
                });
            });

            {{-- 追加/編集モーダル --}}
            $('#btn-add-destination').on('click', function () {
                $('#destination-modal-title').text('行き先を追加');
                $('#form-destination').attr('action', '{{ route('transit.destination.add') }}');
                $('#destination-id').val('');
                $('#destination-label').val('');
                $('#destination-route').val('');
                $('#destination-amount').val('');
                $('#modal-destination').removeClass('hidden');
            });
            $('.edit-destination').on('click', function () {
                $('#destination-modal-title').text('行き先を編集');
                $('#form-destination').attr('action', '{{ route('transit.destination.update') }}');
                $('#destination-id').val($(this).data('id'));
                $('#destination-label').val($(this).data('label'));
                $('#destination-route').val($(this).data('route'));
                $('#destination-amount').val($(this).data('amount'));
                $('#modal-destination').removeClass('hidden');
            });
            $('#close-destination-modal, #cancel-destination-modal').on('click', function () {
                $('#modal-destination').addClass('hidden');
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

            {{-- アクションボタン --}}
            <div class="mb-4 flex flex-wrap gap-2 items-center justify-between">
                <button type="button" onclick="location.href='{{ route('transit') }}'" class="btn-alternative !mr-0 !mb-0">
                    <i class="ti ti-arrow-left"></i>&nbsp;交通費管理へ戻る
                </button>
                <button type="button" id="btn-add-destination" class="btn-blue !mr-0 !mb-0">
                    <i class="ti ti-plus"></i>&nbsp;行き先を追加
                </button>
            </div>

            {{-- 行き先一覧 --}}
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    <i class="ti ti-map-pin"></i>&nbsp;行き先
                    <span class="text-xs font-normal text-gray-400 ml-1">({{ count($destinations) }}件)</span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    ここで登録した行き先が交通費登録の選択肢になります（この並び順で表示されます）<br>
                    ピン留めすると交通費管理画面のクイック登録に表示され、1クリックで登録できます
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4 border border-gray-300 dark:border-gray-500">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300 border-b border-gray-300 dark:border-gray-500">
                            <tr>
                                <th scope="col" class="py-3 px-4">並び</th>
                                <th scope="col" class="py-3 px-4">行き先</th>
                                <th scope="col" class="py-3 px-4">経路</th>
                                <th scope="col" class="py-3 px-4 text-right">金額</th>
                                <th scope="col" class="py-3 px-4 text-center">ピン</th>
                                <th scope="col" class="py-3 px-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($destinations as $index => $destination)
                            <tr class="bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 {{ $destination->is_pinned ? 'row-pinned' : '' }}">
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($index > 0)
                                    <button class="move-destination btn-alternative !mr-1 !mb-0 !py-1.5 !px-2" data-id="{{ $destination->id }}" data-direction="up" title="上へ">
                                        <i class="ti ti-arrow-up"></i>
                                    </button>
                                    @else
                                    <button class="btn-disabled !mr-1 !mb-0 !py-1.5 !px-2" disabled><i class="ti ti-arrow-up"></i></button>
                                    @endif
                                    @if($index < count($destinations) - 1)
                                    <button class="move-destination btn-alternative !mr-0 !mb-0 !py-1.5 !px-2" data-id="{{ $destination->id }}" data-direction="down" title="下へ">
                                        <i class="ti ti-arrow-down"></i>
                                    </button>
                                    @else
                                    <button class="btn-disabled !mr-0 !mb-0 !py-1.5 !px-2" disabled><i class="ti ti-arrow-down"></i></button>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-medium text-gray-900 dark:text-white">{{ $destination->label }}</td>
                                <td class="py-3 px-4 text-xs">{{ $destination->route }}</td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">{{ number_format($destination->amount) }}円</td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <button class="pin-destination {{ $destination->is_pinned ? 'btn-pin-on' : 'btn-pin-off' }}" data-id="{{ $destination->id }}" title="{{ $destination->is_pinned ? 'ピン留めを解除する' : 'クイック登録にピン留めする' }}">
                                        <i class="ti {{ $destination->is_pinned ? 'ti-pinned' : 'ti-pin' }}"></i>
                                    </button>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <button class="edit-destination btn-green-g mr-2" data-id="{{ $destination->id }}" data-label="{{ $destination->label }}" data-route="{{ $destination->route }}" data-amount="{{ $destination->amount }}">
                                        <i class="ti ti-edit"></i>&nbsp;編集
                                    </button>
                                    <button class="delete-destination btn-red-g" data-id="{{ $destination->id }}">
                                        <i class="ti ti-trash"></i>&nbsp;削除
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr class="bg-white dark:bg-gray-800">
                                <td colspan="6" class="py-4 px-4 text-center text-gray-400">行き先は登録されていません</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- 行き先追加/編集モーダル --}}
    <div id="modal-destination" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h3 id="destination-modal-title" class="text-lg font-semibold text-gray-900 dark:text-white">行き先を追加</h3>
                <button id="close-destination-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="ti ti-x text-xl"></i>
                </button>
            </div>
            <form id="form-destination" method="POST" action="{{ route('transit.destination.add') }}">
                @csrf
                <input type="hidden" id="destination-id" name="destination_id" value="">
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">行き先</label>
                        <input type="text" id="destination-label" name="label" maxlength="50" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="例: 自宅→本社" required>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">行きと帰りは別々に登録してください</p>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">経路</label>
                        <input type="text" id="destination-route" name="route" maxlength="100" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="例：○○→△△" required>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">金額（円）</label>
                        <input type="number" id="destination-amount" name="amount" step="1" min="0" max="1000000" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t dark:border-gray-700">
                    <button type="button" id="cancel-destination-modal" class="btn-alternative !mr-0 !mb-0">キャンセル</button>
                    <button type="submit" class="btn-blue !mr-0 !mb-0">保存</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
