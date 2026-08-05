<?php

namespace App\Http\Controllers;

use App\Libs\Common;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function show(Request $request){
        $today_info = Common::getTimeInfo();
        $now = new Carbon('now');
        $current_now = $now->copy();
        $summary_current_year = $now->year;
        $summary_current_month = $now->month;
        $summary_y = $request->input('summary_y');
        $summary_m = $request->input('summary_m');
        if (!is_null($summary_y) && !is_null($summary_m)){
            $summary_current_year = $summary_y;
            $summary_current_month = $summary_m;
            $current_now = new Carbon($summary_y.'-'.$summary_m);
        }
        $summary_calendar = Common::generateCalendar($summary_current_year,$summary_current_month);
        Common::setDatabaseData($summary_calendar,$summary_current_year,$summary_current_month);
        $summary_info = Common::getSummaryInfo($now,$summary_calendar,$current_now);

        // 休暇残数サマリ
        $leaveService = new LeaveService();
        $userId = Auth::id();
        $settings = $leaveService->getOrCreateSettings($userId);
        $startMonth = $settings->fiscal_year_start_month;
        $currentFY = $leaveService->getCurrentFiscalYear($startMonth);
        $paidBalance = $leaveService->calculatePaidLeaveBalance($userId, $currentFY, $startMonth);
        $annualBalance = $leaveService->calculateAnnualLeaveBalance($userId, $currentFY);
        $leaveSummary = compact('currentFY', 'paidBalance', 'annualBalance');

        $param = compact('today_info','summary_info','leaveSummary');
        return view('home', $param);
    }
}
