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
        $summary_calendar = Common::generateCalendar($now->year,$now->month);
        Common::setDatabaseData($summary_calendar,$now->year,$now->month);
        $summary_info = Common::getSummaryInfo($now,$summary_calendar);
        $param = compact('today_info','summary_info');
        return view('home', $param);
    }
}
