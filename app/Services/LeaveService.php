<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\LeaveSetting;
use App\Models\LeaveGrant;
use App\Models\LeaveUsage;

class LeaveService
{
    // 休暇種別定数
    const TYPE_PAID = 'paid';             // 有休
    const TYPE_ANNUAL = 'annual';         // 年次休暇
    const TYPE_COMPENSATORY = 'compensatory'; // 代休
    const TYPE_EXPIRED_STOCK = 'expired_stock'; // 失効累積

    const TYPE_LABELS = [
        self::TYPE_PAID => '有休',
        self::TYPE_ANNUAL => '年次休暇',
        self::TYPE_COMPENSATORY => '代休',
        self::TYPE_EXPIRED_STOCK => '失効累積',
    ];

    /**
     * ユーザの休暇設定を取得（なければデフォルトで作成）
     */
    public function getOrCreateSettings(int $userId): LeaveSetting
    {
        return LeaveSetting::firstOrCreate(
            ['user_id' => $userId],
            [
                'fiscal_year_start_month' => 4,
                'paid_leave_auto_grant' => true,
                'paid_leave_grant_days' => 20.0,
                'annual_leave_grant_days' => 0.0,
            ]
        );
    }

    /**
     * 指定日が属する年度を返す
     */
    public function getFiscalYear(Carbon $date, int $startMonth): int
    {
        if ($startMonth === 1) {
            return $date->year;
        }
        return $date->month >= $startMonth ? $date->year : $date->year - 1;
    }

    /**
     * 現在の年度を返す
     */
    public function getCurrentFiscalYear(int $startMonth): int
    {
        return $this->getFiscalYear(Carbon::now(), $startMonth);
    }

    /**
     * 年度の開始日を返す
     */
    public function getFiscalYearStartDate(int $fiscalYear, int $startMonth): Carbon
    {
        return Carbon::create($fiscalYear, $startMonth, 1);
    }

    /**
     * 年度の終了日を返す
     */
    public function getFiscalYearEndDate(int $fiscalYear, int $startMonth): Carbon
    {
        $start = $this->getFiscalYearStartDate($fiscalYear, $startMonth);
        return $start->copy()->addYear()->subDay();
    }

    /**
     * 有休の有効期限を計算（付与年度の翌年度末）
     */
    public function getPaidLeaveExpiryDate(int $grantFiscalYear, int $startMonth): Carbon
    {
        return $this->getFiscalYearEndDate($grantFiscalYear + 1, $startMonth);
    }

    /**
     * 年次休暇の有効期限を計算（付与年度末）
     */
    public function getAnnualLeaveExpiryDate(int $grantFiscalYear, int $startMonth): Carbon
    {
        return $this->getFiscalYearEndDate($grantFiscalYear, $startMonth);
    }

    /**
     * 自動付与が必要かチェックし、必要な付与情報を返す
     * @return array 付与が必要な項目の配列（空なら付与不要）
     */
    public function checkAutoGrantNeeded(int $userId, LeaveSetting $settings): array
    {
        $currentFY = $this->getCurrentFiscalYear($settings->fiscal_year_start_month);
        $needed = [];

        // 該当年度で拒否済みならスキップ
        if ($settings->auto_grant_dismissed_fy == $currentFY) {
            return $needed;
        }

        // 有休の自動付与チェック
        if ($settings->paid_leave_auto_grant && $settings->paid_leave_grant_days > 0) {
            $exists = LeaveGrant::where('user_id', $userId)
                ->where('leave_type', self::TYPE_PAID)
                ->where('fiscal_year', $currentFY)
                ->where('is_auto', true)
                ->exists();
            if (!$exists) {
                $needed[] = [
                    'type' => self::TYPE_PAID,
                    'label' => self::TYPE_LABELS[self::TYPE_PAID],
                    'days' => $settings->paid_leave_grant_days,
                    'fiscal_year' => $currentFY,
                ];
            }
        }

        // 年次休暇の自動付与チェック
        if ($settings->annual_leave_grant_days > 0) {
            $exists = LeaveGrant::where('user_id', $userId)
                ->where('leave_type', self::TYPE_ANNUAL)
                ->where('fiscal_year', $currentFY)
                ->where('is_auto', true)
                ->exists();
            if (!$exists) {
                $needed[] = [
                    'type' => self::TYPE_ANNUAL,
                    'label' => self::TYPE_LABELS[self::TYPE_ANNUAL],
                    'days' => $settings->annual_leave_grant_days,
                    'fiscal_year' => $currentFY,
                ];
            }
        }

        return $needed;
    }

    /**
     * 自動付与を実行
     */
    public function executeAutoGrant(int $userId, LeaveSetting $settings, array $grantItems): void
    {
        $startMonth = $settings->fiscal_year_start_month;

        foreach ($grantItems as $item) {
            $fiscalYear = $item['fiscal_year'];
            $effectiveDate = $this->getFiscalYearStartDate($fiscalYear, $startMonth);

            if ($item['type'] === self::TYPE_PAID) {
                $expiryDate = $this->getPaidLeaveExpiryDate($fiscalYear, $startMonth);
            } else {
                $expiryDate = $this->getAnnualLeaveExpiryDate($fiscalYear, $startMonth);
            }

            LeaveGrant::create([
                'user_id' => $userId,
                'leave_type' => $item['type'],
                'fiscal_year' => $fiscalYear,
                'grant_days' => $item['days'],
                'effective_date' => $effectiveDate,
                'expiry_date' => $expiryDate,
                'is_auto' => true,
                'note' => '自動付与',
            ]);
        }
    }

    /**
     * 選択年度の基準日を返す
     * 過去年度 → 年度末、現在・未来年度 → 今日
     */
    public function getReferenceDate(int $fiscalYear, int $startMonth): Carbon
    {
        $today = Carbon::today();
        $fyEnd = $this->getFiscalYearEndDate($fiscalYear, $startMonth);
        return $today->lt($fyEnd) ? $today : $fyEnd;
    }

    /**
     * 有休の残数を計算（使用日時点で有効な付与から期限順に消費）
     * 基準日（年度末 or 今日）までの使用・付与で計算する
     */
    public function calculatePaidLeaveBalance(int $userId, int $fiscalYear, int $startMonth, ?Carbon $refDate = null): array
    {
        $refDate = $refDate ?? $this->getReferenceDate($fiscalYear, $startMonth);

        // 基準日までに有効になった有休付与を期限順に取得
        $grants = LeaveGrant::where('user_id', $userId)
            ->where('leave_type', self::TYPE_PAID)
            ->whereNotNull('expiry_date')
            ->where('effective_date', '<=', $refDate)
            ->orderBy('expiry_date', 'asc')
            ->get();

        // 付与ごとの残数を初期化
        $grantRemaining = [];
        foreach ($grants as $grant) {
            $grantRemaining[$grant->id] = (float) $grant->grant_days;
        }

        // 基準日までの有休使用を日付順に取得
        $usages = LeaveUsage::where('user_id', $userId)
            ->where('leave_type', self::TYPE_PAID)
            ->where('usage_date', '<=', $refDate)
            ->orderBy('usage_date', 'asc')
            ->get();

        // 各使用を、使用日時点で有効かつ期限の早い付与から消費
        foreach ($usages as $usage) {
            $usageDays = (float) $usage->days;
            $usageDate = $usage->usage_date;

            foreach ($grants as $grant) {
                if ($usageDays <= 0) break;
                if ($grant->expiry_date < $usageDate) continue;
                if ($grantRemaining[$grant->id] <= 0) continue;

                $consume = min($usageDays, $grantRemaining[$grant->id]);
                $grantRemaining[$grant->id] -= $consume;
                $usageDays -= $consume;
            }
        }

        // 付与別の結果を構築
        $balanceByGrant = [];
        foreach ($grants as $grant) {
            $remaining = $grantRemaining[$grant->id];
            $isExpired = $grant->expiry_date < $refDate;

            // 基準日時点で期限切れなら残はゼロ
            if ($isExpired) {
                $remaining = 0;
            }

            $balanceByGrant[] = [
                'grant_id' => $grant->id,
                'fiscal_year' => $grant->fiscal_year,
                'grant_days' => (float) $grant->grant_days,
                'consumed' => (float) $grant->grant_days - $grantRemaining[$grant->id],
                'remaining' => $remaining,
                'expiry_date' => $grant->expiry_date,
                'is_expired' => $isExpired,
            ];
        }

        // 今年度分と昨年度分に分類
        $currentFYRemaining = 0;
        $currentFYGrant = 0;
        $prevFYRemaining = 0;
        $prevFYGrant = 0;
        $prevFY = $fiscalYear - 1;

        foreach ($balanceByGrant as $b) {
            if ($b['fiscal_year'] == $fiscalYear) {
                $currentFYRemaining += $b['remaining'];
                $currentFYGrant += $b['grant_days'];
            } elseif ($b['fiscal_year'] == $prevFY) {
                $prevFYRemaining += $b['remaining'];
                $prevFYGrant += $b['grant_days'];
            }
        }

        return [
            'current_fy' => [
                'fiscal_year' => $fiscalYear,
                'grant_days' => $currentFYGrant,
                'remaining' => $currentFYRemaining,
            ],
            'prev_fy' => [
                'fiscal_year' => $prevFY,
                'grant_days' => $prevFYGrant,
                'remaining' => $prevFYRemaining,
            ],
            'total_remaining' => $currentFYRemaining + $prevFYRemaining,
            'total_grant' => $currentFYGrant + $prevFYGrant,
            'detail' => $balanceByGrant,
        ];
    }

    /**
     * 年次休暇の残数を計算
     */
    public function calculateAnnualLeaveBalance(int $userId, int $fiscalYear, int $startMonth = 0, ?Carbon $refDate = null): array
    {
        $totalGrant = LeaveGrant::where('user_id', $userId)
            ->where('leave_type', self::TYPE_ANNUAL)
            ->where('fiscal_year', $fiscalYear)
            ->sum('grant_days');

        if ($refDate !== null && $startMonth > 0) {
            // 基準日が指定された場合: 年度開始日〜基準日の使用を集計
            $fyStart = $this->getFiscalYearStartDate($fiscalYear, $startMonth);
            $totalUsage = (float) LeaveUsage::where('user_id', $userId)
                ->where('leave_type', self::TYPE_ANNUAL)
                ->where('usage_date', '>=', $fyStart)
                ->where('usage_date', '<=', $refDate)
                ->sum('days');
        } else {
            // 従来動作: 年度全体の使用を集計
            $totalUsage = $this->getUsageForFiscalYear($userId, self::TYPE_ANNUAL, $fiscalYear, 0, 0);
        }

        return [
            'fiscal_year' => $fiscalYear,
            'grant_days' => (float) $totalGrant,
            'used_days' => $totalUsage,
            'remaining' => (float) $totalGrant - $totalUsage,
        ];
    }

    /**
     * 代休の残数を計算
     * 代休は期限なしだが、基準日までの付与・使用で計算する
     */
    public function calculateCompensatoryBalance(int $userId, int $fiscalYear, int $startMonth, ?Carbon $refDate = null): array
    {
        $refDate = $refDate ?? $this->getReferenceDate($fiscalYear, $startMonth);

        $totalGrant = LeaveGrant::where('user_id', $userId)
            ->where('leave_type', self::TYPE_COMPENSATORY)
            ->where('effective_date', '<=', $refDate)
            ->sum('grant_days');

        $totalUsage = LeaveUsage::where('user_id', $userId)
            ->where('leave_type', self::TYPE_COMPENSATORY)
            ->where('usage_date', '<=', $refDate)
            ->sum('days');

        return [
            'grant_days' => (float) $totalGrant,
            'used_days' => (float) $totalUsage,
            'remaining' => (float) $totalGrant - (float) $totalUsage,
        ];
    }

    /**
     * 失効累積（有休の失効分ストック）を計算
     * calculatePaidLeaveBalance の detail を再利用し、基準日時点で期限切れの未消費分を合計。
     */
    public function calculateExpiredStock(int $userId, int $fiscalYear, int $startMonth, array $paidBalanceDetail, ?Carbon $refDate = null): array
    {
        $refDate = $refDate ?? $this->getReferenceDate($fiscalYear, $startMonth);

        $expiredDays = 0;
        foreach ($paidBalanceDetail as $d) {
            if ($d['is_expired']) {
                $expiredDays += ($d['grant_days'] - $d['consumed']);
            }
        }

        // 基準日までの失効累積使用分を差し引く
        $expiredStockUsage = (float) LeaveUsage::where('user_id', $userId)
            ->where('leave_type', self::TYPE_EXPIRED_STOCK)
            ->where('usage_date', '<=', $refDate)
            ->sum('days');

        return [
            'expired_days' => $expiredDays,
            'used_days' => $expiredStockUsage,
            'remaining' => $expiredDays - $expiredStockUsage,
        ];
    }

    /**
     * 月別使用集計を取得
     */
    public function getMonthlyReport(int $userId, int $fiscalYear, int $startMonth, ?Carbon $refDate = null): array
    {
        $fyStart = $this->getFiscalYearStartDate($fiscalYear, $startMonth);
        $fyEnd = $this->getFiscalYearEndDate($fiscalYear, $startMonth);

        // 年度内の全使用記録を取得
        $usages = LeaveUsage::where('user_id', $userId)
            ->whereBetween('usage_date', [$fyStart, $fyEnd])
            ->orderBy('usage_date')
            ->get();

        // 月ごとに集計
        $months = [];
        $cursor = $fyStart->copy();
        for ($i = 0; $i < 12; $i++) {
            $monthKey = $cursor->format('Y-m');
            $months[$monthKey] = [
                'month' => $cursor->month,
                'year' => $cursor->year,
                'label' => $cursor->format('n') . '月',
                'paid' => 0,
                'annual' => 0,
                'compensatory' => 0,
                'paid_future' => 0,
                'annual_future' => 0,
                'compensatory_future' => 0,
            ];
            $cursor->addMonth();
        }

        foreach ($usages as $usage) {
            $key = $usage->usage_date->format('Y-m');
            if (isset($months[$key])) {
                $months[$key][$usage->leave_type] += (float) $usage->days;
                // 基準日より後の使用を未加算分として追跡
                if ($refDate !== null && $usage->usage_date->gt($refDate)) {
                    $futureKey = $usage->leave_type . '_future';
                    if (isset($months[$key][$futureKey])) {
                        $months[$key][$futureKey] += (float) $usage->days;
                    }
                }
            }
        }

        // 有休の月別残数を計算（累積減算）
        $paidBalance = $this->calculatePaidLeaveBalance($userId, $fiscalYear, $startMonth);
        $paidTotal = $paidBalance['total_grant'];

        // 年度開始前の有休使用合計を算出（残数の起点にする）
        $priorPaidUsage = LeaveUsage::where('user_id', $userId)
            ->where('leave_type', self::TYPE_PAID)
            ->where('usage_date', '<', $fyStart)
            ->sum('days');

        // 有効な付与の合計（この年度で有効なもの = 今年度付与 + 昨年度付与）
        $effectiveGrant = $paidBalance['total_grant'];
        $paidRunning = $effectiveGrant - (float) $priorPaidUsage;

        // 昨年度分の残を月別に追跡（期限順消費: 昨年度分から先に消費）
        $prevFYGrant = $paidBalance['prev_fy']['grant_days'];
        $paidCumulativeUsage = (float) $priorPaidUsage;
        // 昨年度分から消費されるので、昨年度分の残 = 昨年度付与 - min(昨年度付与, 累積使用)
        $prevFYRunning = $prevFYGrant - min($prevFYGrant, $paidCumulativeUsage);

        // 年次休暇
        $annualBalance = $this->calculateAnnualLeaveBalance($userId, $fiscalYear);
        $annualRunning = $annualBalance['grant_days'];

        // 代休
        $compBalance = $this->calculateCompensatoryBalance($userId, $fiscalYear, $startMonth);
        // 代休は期限なしなので、年度開始時点の残を起点に
        $priorCompUsage = LeaveUsage::where('user_id', $userId)
            ->where('leave_type', self::TYPE_COMPENSATORY)
            ->where('usage_date', '<', $fyStart)
            ->sum('days');
        $priorCompGrant = LeaveGrant::where('user_id', $userId)
            ->where('leave_type', self::TYPE_COMPENSATORY)
            ->where('effective_date', '<', $fyStart)
            ->sum('grant_days');
        $compRunning = (float) $priorCompGrant - (float) $priorCompUsage;

        // 年度内の代休付与も加算
        $monthlyReport = [];
        foreach ($months as $key => $month) {
            // 当月の代休付与を加算
            $monthCompGrant = LeaveGrant::where('user_id', $userId)
                ->where('leave_type', self::TYPE_COMPENSATORY)
                ->whereYear('effective_date', $month['year'])
                ->whereMonth('effective_date', $month['month'])
                ->sum('grant_days');
            $compRunning += (float) $monthCompGrant;

            // 当月の年次休暇手動付与も加算（年度内の手動追加分）
            // ※自動付与は年度初に既に含まれているので is_auto=false のみ
            // → 年次休暇の残数は grant_days 合計 - usage 合計で計算するため
            //   ここでは単純に usage 分を引く

            $paidRunning -= $month['paid'];
            $paidCumulativeUsage += $month['paid'];
            $prevFYRunning = $prevFYGrant - min($prevFYGrant, $paidCumulativeUsage);
            $annualRunning -= $month['annual'];
            $compRunning -= $month['compensatory'];

            $monthlyReport[] = [
                'label' => $month['label'],
                'year' => $month['year'],
                'month' => $month['month'],
                'paid_used' => $month['paid'],
                'paid_future' => $month['paid_future'],
                'paid_prev_fy_remaining' => round($prevFYRunning, 1),
                'paid_remaining' => round($paidRunning, 1),
                'annual_used' => $month['annual'],
                'annual_future' => $month['annual_future'],
                'annual_remaining' => round($annualRunning, 1),
                'comp_used' => $month['compensatory'],
                'comp_future' => $month['compensatory_future'],
                'comp_remaining' => round($compRunning, 1),
            ];
        }

        return $monthlyReport;
    }

    /**
     * 指定年度・種別の使用合計を取得
     */
    private function getUsageForFiscalYear(int $userId, string $type, int $fiscalYear, int $startMonthUnused1, int $startMonthUnused2): float
    {
        // 年次休暇の場合: その年度の付与に紐づく使用を全て合計
        // usage_date による集計ではなく、leave_type で絞る
        // （年次休暇は年度を跨いで使うことはないので、全使用=年度内使用）
        // ただし複数年度の付与がある場合は区別が必要
        // → シンプルに: 年次休暇の usage で usage_date が年度内のものを合計
        $settings = LeaveSetting::where('user_id', $userId)->first();
        $startMonth = $settings ? $settings->fiscal_year_start_month : 4;

        $fyStart = $this->getFiscalYearStartDate($fiscalYear, $startMonth);
        $fyEnd = $this->getFiscalYearEndDate($fiscalYear, $startMonth);

        return (float) LeaveUsage::where('user_id', $userId)
            ->where('leave_type', $type)
            ->whereBetween('usage_date', [$fyStart, $fyEnd])
            ->sum('days');
    }

    /**
     * 使用履歴を取得
     */
    public function getUsageHistory(int $userId, int $fiscalYear, int $startMonth): array
    {
        $fyStart = $this->getFiscalYearStartDate($fiscalYear, $startMonth);
        $fyEnd = $this->getFiscalYearEndDate($fiscalYear, $startMonth);

        $usages = LeaveUsage::where('user_id', $userId)
            ->whereBetween('usage_date', [$fyStart, $fyEnd])
            ->orderBy('usage_date', 'desc')
            ->get();

        return $usages->map(function ($usage) {
            return [
                'id' => $usage->id,
                'leave_type' => $usage->leave_type,
                'type_label' => self::TYPE_LABELS[$usage->leave_type] ?? $usage->leave_type,
                'usage_date' => $usage->usage_date->format('Y/m/d'),
                'days' => (float) $usage->days,
                'note' => $usage->note,
            ];
        })->toArray();
    }

    /**
     * 付与履歴を取得
     */
    public function getGrantHistory(int $userId, int $fiscalYear, int $startMonth): array
    {
        $fyStart = $this->getFiscalYearStartDate($fiscalYear, $startMonth);
        $fyEnd = $this->getFiscalYearEndDate($fiscalYear, $startMonth);

        $grants = LeaveGrant::where('user_id', $userId)
            ->where(function ($query) use ($fiscalYear, $fyStart, $fyEnd) {
                // 有休・年次休暇: fiscal_year が一致するもの
                $query->where(function ($q) use ($fiscalYear) {
                    $q->whereIn('leave_type', [self::TYPE_PAID, self::TYPE_ANNUAL])
                      ->where('fiscal_year', $fiscalYear);
                })
                // 代休: effective_date が年度内のもの
                ->orWhere(function ($q) use ($fyStart, $fyEnd) {
                    $q->where('leave_type', self::TYPE_COMPENSATORY)
                      ->whereBetween('effective_date', [$fyStart, $fyEnd]);
                });
            })
            ->orderBy('effective_date', 'desc')
            ->get();

        return $grants->map(function ($grant) {
            return [
                'id' => $grant->id,
                'leave_type' => $grant->leave_type,
                'type_label' => self::TYPE_LABELS[$grant->leave_type] ?? $grant->leave_type,
                'fiscal_year' => $grant->fiscal_year,
                'grant_days' => (float) $grant->grant_days,
                'effective_date' => $grant->effective_date->format('Y/m/d'),
                'expiry_date' => $grant->expiry_date ? $grant->expiry_date->format('Y/m/d') : 'なし',
                'is_auto' => $grant->is_auto,
                'note' => $grant->note,
            ];
        })->toArray();
    }

    /**
     * 選択可能な年度リストを生成
     */
    public function getFiscalYearList(int $startMonth, int $rangeBack = 3, int $rangeForward = 1): array
    {
        $currentFY = $this->getCurrentFiscalYear($startMonth);
        $years = [];
        for ($fy = $currentFY - $rangeBack; $fy <= $currentFY + $rangeForward; $fy++) {
            $years[] = $fy;
        }
        return $years;
    }
}
