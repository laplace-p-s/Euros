<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ToolsController extends Controller
{
    public function index(Request $request){
        return view('tools');
    }

    public function paid_leave_show(Request $request){
//        //画面生成
//        $result_list = $this->get_holiday_list();
//        $param = compact('result_list');
//        return view('master_holiday',$param);
        return view('paid_leave');
    }
}
