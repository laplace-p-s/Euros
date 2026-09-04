<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TransitDestination;
use App\Models\TransitRecord;
use App\Services\TransitService;

class TransitController extends Controller
{
    private TransitService $transitService;

    public function __construct(TransitService $transitService)
    {
        $this->transitService = $transitService;
    }

    /**
     * メイン画面
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // 表示月の決定（前月/翌月ボタンからの遷移も含む）
        $selectedMonth = $this->transitService->resolveMonth($request->input('month'));
        if ($request->input('action') === 'back') {
            $selectedMonth->subMonth();
        } elseif ($request->input('action') === 'next') {
            $selectedMonth->addMonth();
        }

        $records = $this->transitService->getRecords($userId, $selectedMonth);
        $total = $this->transitService->getTotal($records);
        $destinations = $this->transitService->getDestinations($userId);
        $monthList = $this->transitService->getMonthList($userId, $selectedMonth);

        // 登録モーダルの日付初期値（当月表示なら今日、それ以外は表示月の1日）
        $defaultDate = $selectedMonth->isSameMonth(Carbon::now())
            ? Carbon::now()->format('Y-m-d')
            : $selectedMonth->format('Y-m-d');

        $selectedMonthValue = $selectedMonth->format('Y-m');
        $selectedMonthLabel = $selectedMonth->format('Y年m月');

        $param = compact(
            'selectedMonthValue', 'selectedMonthLabel', 'monthList',
            'records', 'total', 'destinations', 'defaultDate'
        );

        return view('transit', $param);
    }

    /**
     * 交通費の登録
     */
    public function addRecord(Request $request)
    {
        $request->validate([
            'use_date' => 'required|date',
            'destination_id' => 'required|integer',
            'route' => 'required|string|max:100',
            'amount' => 'required|integer|min:0|max:1000000',
            'note' => 'nullable|string|max:100',
        ]);

        $userId = Auth::id();

        // 行き先は自分が登録したものに限る
        $destination = TransitDestination::where('id', $request->input('destination_id'))
            ->where('user_id', $userId)
            ->first();

        if (is_null($destination)) {
            return redirect()->route('transit', ['month' => $request->input('month')])
                ->with('message', '行き先が見つかりません');
        }

        TransitRecord::create([
            'user_id' => $userId,
            'use_date' => $request->input('use_date'),
            'label' => $destination->label, // 行き先名は登録時点の内容を保持
            'route' => $request->input('route'),
            'amount' => $request->input('amount'),
            'note' => $request->input('note'),
            'destination_id' => $destination->id,
        ]);

        return redirect()->route('transit', ['month' => $request->input('month')])
            ->with('message', '交通費を登録しました');
    }

    /**
     * 交通費の削除
     */
    public function deleteRecord(Request $request)
    {
        $record = TransitRecord::where('id', $request->input('record_id'))
            ->where('user_id', Auth::id())
            ->first();

        if ($record) {
            $record->delete();
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error', 'message' => '対象が見つかりません'], 404);
    }

    /**
     * 行き先の管理画面
     */
    public function destinationIndex(Request $request)
    {
        $destinations = $this->transitService->getDestinations(Auth::id());

        return view('transit_destination', compact('destinations'));
    }

    /**
     * 行き先の追加
     */
    public function addDestination(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:50',
            'route' => 'required|string|max:100',
            'amount' => 'required|integer|min:0|max:1000000',
        ]);

        TransitDestination::create([
            'user_id' => Auth::id(),
            'label' => $request->input('label'),
            'route' => $request->input('route'),
            'amount' => $request->input('amount'),
        ]);

        return redirect()->route('transit.destination')
            ->with('message', '行き先を追加しました');
    }

    /**
     * 行き先の更新
     */
    public function updateDestination(Request $request)
    {
        $request->validate([
            'destination_id' => 'required|integer',
            'label' => 'required|string|max:50',
            'route' => 'required|string|max:100',
            'amount' => 'required|integer|min:0|max:1000000',
        ]);

        $destination = TransitDestination::where('id', $request->input('destination_id'))
            ->where('user_id', Auth::id())
            ->first();

        if ($destination) {
            $destination->label = $request->input('label');
            $destination->route = $request->input('route');
            $destination->amount = $request->input('amount');
            $destination->save();
        }

        return redirect()->route('transit.destination')
            ->with('message', '行き先を更新しました');
    }

    /**
     * 行き先の削除
     */
    public function deleteDestination(Request $request)
    {
        $destination = TransitDestination::where('id', $request->input('destination_id'))
            ->where('user_id', Auth::id())
            ->first();

        if ($destination) {
            $destination->delete();
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error', 'message' => '対象が見つかりません'], 404);
    }
}
