<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveGrant;
use App\Models\LeaveUsage;
use App\Services\LeaveService;

class LeaveController extends Controller
{
    private LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    /**
     * メイン画面
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $settings = $this->leaveService->getOrCreateSettings($userId);
        $startMonth = $settings->fiscal_year_start_month;

        // 表示年度の決定
        $selectedFY = $request->input('fiscal_year', $this->leaveService->getCurrentFiscalYear($startMonth));

        // 自動付与チェック（現在年度のみ）
        $autoGrantNeeded = [];
        $currentFY = $this->leaveService->getCurrentFiscalYear($startMonth);
        if ($selectedFY == $currentFY) {
            $autoGrantNeeded = $this->leaveService->checkAutoGrantNeeded($userId, $settings);
        }

        // 基準日モード（today: 今日現在 / fy_end: 年度末）
        // URLパラメータ → Cookie → デフォルト(today) の優先順で決定
        $cookieName = "leave_ref_mode_{$userId}";
        if ($request->has('ref_mode')) {
            $refMode = $request->input('ref_mode');
        } else {
            $refMode = $request->cookie($cookieName, 'today');
        }
        $refMode = in_array($refMode, ['today', 'fy_end']) ? $refMode : 'today';

        $fyEndDate = $this->leaveService->getFiscalYearEndDate($selectedFY, $startMonth);
        if ($refMode === 'fy_end') {
            $referenceDate = $fyEndDate;
        } else {
            $referenceDate = $this->leaveService->getReferenceDate($selectedFY, $startMonth);
        }

        // 各種残数計算（全て同じ基準日で統一）
        $paidBalance = $this->leaveService->calculatePaidLeaveBalance($userId, $selectedFY, $startMonth, $referenceDate);
        $annualBalance = $this->leaveService->calculateAnnualLeaveBalance($userId, $selectedFY, $startMonth, $referenceDate);
        $compBalance = $this->leaveService->calculateCompensatoryBalance($userId, $selectedFY, $startMonth, $referenceDate);

        // 失効累積（paidBalance の detail を再利用）
        $showExpiredStock = $settings->show_expired_stock;
        $expiredStock = $showExpiredStock
            ? $this->leaveService->calculateExpiredStock($userId, $selectedFY, $startMonth, $paidBalance['detail'], $referenceDate)
            : null;

        // 月別レポート（基準日を渡して未加算分を追跡）
        $monthlyReport = $this->leaveService->getMonthlyReport($userId, $selectedFY, $startMonth, $referenceDate);

        // 使用履歴
        $usageHistory = $this->leaveService->getUsageHistory($userId, $selectedFY, $startMonth);

        // 付与履歴
        $grantHistory = $this->leaveService->getGrantHistory($userId, $selectedFY, $startMonth);

        // 年度リスト
        $fiscalYears = $this->leaveService->getFiscalYearList($startMonth);

        $param = compact(
            'settings', 'selectedFY', 'currentFY', 'startMonth',
            'refMode', 'referenceDate',
            'autoGrantNeeded', 'paidBalance', 'annualBalance', 'compBalance',
            'showExpiredStock', 'expiredStock',
            'monthlyReport', 'usageHistory', 'grantHistory', 'fiscalYears'
        );

        // 基準日モードをCookieに保存（1年間有効）
        return response()
            ->view('leave', $param)
            ->cookie($cookieName, $refMode, 60 * 24 * 365);
    }

    /**
     * 自動付与実行
     */
    public function executeAutoGrant(Request $request)
    {
        $userId = Auth::id();
        $settings = $this->leaveService->getOrCreateSettings($userId);
        $autoGrantNeeded = $this->leaveService->checkAutoGrantNeeded($userId, $settings);

        if (!empty($autoGrantNeeded)) {
            $this->leaveService->executeAutoGrant($userId, $settings, $autoGrantNeeded);
        }

        return redirect()->route('leave')->with('message', '自動付与を実行しました');
    }

    /**
     * 自動付与を拒否（今年度は表示しない）
     */
    public function dismissAutoGrant(Request $request)
    {
        $userId = Auth::id();
        $settings = $this->leaveService->getOrCreateSettings($userId);
        $currentFY = $this->leaveService->getCurrentFiscalYear($settings->fiscal_year_start_month);

        $settings->auto_grant_dismissed_fy = $currentFY;
        $settings->save();

        return redirect()->route('leave')->with('message', '自動付与を今年度はスキップしました');
    }

    /**
     * 使用登録
     */
    public function addUsage(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:paid,annual,compensatory,expired_stock',
            'usage_date' => 'required|date',
            'days' => 'required|numeric|in:0.5,1',
            'note' => 'nullable|string|max:100',
        ]);

        LeaveUsage::create([
            'user_id' => Auth::id(),
            'leave_type' => $request->input('leave_type'),
            'usage_date' => $request->input('usage_date'),
            'days' => $request->input('days'),
            'note' => $request->input('note'),
        ]);

        return redirect()->route('leave', ['fiscal_year' => $request->input('fiscal_year')])
            ->with('message', '休暇使用を登録しました');
    }

    /**
     * 使用削除
     */
    public function deleteUsage(Request $request)
    {
        $usage = LeaveUsage::where('id', $request->input('usage_id'))
            ->where('user_id', Auth::id())
            ->first();

        if ($usage) {
            $usage->delete();
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error', 'message' => '対象が見つかりません'], 404);
    }

    /**
     * 付与追加（手動）
     */
    public function addGrant(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:paid,annual,compensatory',
            'fiscal_year' => 'required|integer',
            'grant_days' => 'required|numeric|min:0.5',
            'effective_date' => 'required|date',
            'note' => 'nullable|string|max:100',
        ]);

        $userId = Auth::id();
        $settings = $this->leaveService->getOrCreateSettings($userId);
        $startMonth = $settings->fiscal_year_start_month;
        $leaveType = $request->input('leave_type');
        $fiscalYear = $request->input('fiscal_year');

        // 有効期限の決定
        $expiryDate = null;
        if ($leaveType === LeaveService::TYPE_PAID) {
            $expiryDate = $this->leaveService->getPaidLeaveExpiryDate($fiscalYear, $startMonth);
        } elseif ($leaveType === LeaveService::TYPE_ANNUAL) {
            $expiryDate = $this->leaveService->getAnnualLeaveExpiryDate($fiscalYear, $startMonth);
        }
        // 代休は期限なし（null）

        LeaveGrant::create([
            'user_id' => $userId,
            'leave_type' => $leaveType,
            'fiscal_year' => $fiscalYear,
            'grant_days' => $request->input('grant_days'),
            'effective_date' => $request->input('effective_date'),
            'expiry_date' => $expiryDate,
            'is_auto' => false,
            'note' => $request->input('note'),
        ]);

        return redirect()->route('leave', ['fiscal_year' => $request->input('selected_fy')])
            ->with('message', '付与を追加しました');
    }

    /**
     * 付与削除
     */
    public function deleteGrant(Request $request)
    {
        $grant = LeaveGrant::where('id', $request->input('grant_id'))
            ->where('user_id', Auth::id())
            ->first();

        if ($grant) {
            $grant->delete();
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error', 'message' => '対象が見つかりません'], 404);
    }
}
