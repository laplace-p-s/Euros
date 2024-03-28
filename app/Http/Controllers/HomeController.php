<?php

namespace App\Http\Controllers;

use App\Libs\Common;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function show(Request $request){
        $today_info = Common::getTimeInfo();
        $now = new Carbon('now');
        $summary_current_year = $now->year;
        $summary_current_month = $now->month;
        $summary_y = $request->input('summary_y');
        $summary_m = $request->input('summary_m');
        if (!is_null($summary_y) && !is_null($summary_m)){
            $summary_current_year = $summary_y;
            $summary_current_month = $summary_m;
        }
        $summary_calendar = Common::generateCalendar($summary_current_year,$summary_current_month);
        Common::setDatabaseData($summary_calendar,$summary_current_year,$summary_current_month);
        $summary_info = Common::getSummaryInfo($now,$summary_calendar,$summary_current_year,$summary_current_month);
        $param = compact('today_info','summary_info');
        return view('home', $param);
    }
}
