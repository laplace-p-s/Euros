<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\TransitDestination;
use App\Models\TransitRecord;

class TransitService
{
    /**
     * 表示対象月を決定する（YYYY-MM形式・不正値は当月）
     */
    public function resolveMonth(?string $month): Carbon
    {
        if (!is_null($month) && preg_match('/^\d{4}-\d{2}$/', $month)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
            } catch (\Exception $e) {
                // 不正な年月はフォールバック
            }
        }
        return Carbon::now()->startOfMonth();
    }

    /**
     * 行き先テンプレート一覧
     */
    public function getDestinations(int $userId)
    {
        return TransitDestination::where('user_id', $userId)
            ->orderBy('id')
            ->get();
    }

    /**
     * 指定月の明細一覧（画面表示用に整形）
     */
    public function getRecords(int $userId, Carbon $month): array
    {
        $records = TransitRecord::where('user_id', $userId)
            ->whereDate('use_date', '>=', $month->copy()->startOfMonth()->format('Y-m-d'))
            ->whereDate('use_date', '<=', $month->copy()->endOfMonth()->format('Y-m-d'))
            ->orderBy('use_date')
            ->orderBy('id')
            ->get();

        $ret = [];
        foreach ($records as $record) {
            $ret[] = [
                'id' => $record->id,
                'use_date' => $record->use_date->isoFormat('YYYY/MM/DD (ddd)'),
                'use_date_raw' => $record->use_date->format('Y-m-d'),
                'week' => $record->use_date->dayOfWeek, //日が0,土が6
                'label' => $record->label,
                'route' => $record->route,
                'amount' => $record->amount,
                'note' => $record->note,
                'destination_id' => $record->destination_id,
            ];
        }
        return $ret;
    }

    /**
     * 明細の合計金額
     */
    public function getTotal(array $records): int
    {
        return array_sum(array_column($records, 'amount'));
    }

    /**
     * 月セレクタ用の年月リスト（最古の登録月〜当月、表示月を含む）
     */
    public function getMonthList(int $userId, Carbon $selectedMonth): array
    {
        $oldest = TransitRecord::where('user_id', $userId)->min('use_date');
        $start = is_null($oldest)
            ? Carbon::now()->startOfMonth()
            : Carbon::parse($oldest)->startOfMonth();

        $end = Carbon::now()->startOfMonth();
        if ($selectedMonth->lt($start)) $start = $selectedMonth->copy();
        if ($selectedMonth->gt($end)) $end = $selectedMonth->copy();

        $list = [];
        $cursor = $end->copy();
        while ($cursor->gte($start)) {
            $list[] = $cursor->format('Y-m');
            $cursor->subMonth();
        }
        return $list;
    }
}
