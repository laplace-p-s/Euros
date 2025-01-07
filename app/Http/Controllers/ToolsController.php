<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\YearlyLeaveCount;

class ToolsController extends Controller
{
    public function index(Request $request){
        return view('tools');
    }

    public function paid_leave_show(Request $request){
        //画面用データ収集
        $yearly_year = $this->get_yearly(); //今年度
        $sub_yearly_year = $yearly_year - 1; //昨年度
        $yearly_leave_count = YearlyLeaveCount::where('user_id',Auth::id())
            ->where('yearly',$yearly_year)
            ->first();
        $sub_yearly_leave_count = YearlyLeaveCount::where('user_id',Auth::id())
            ->where('yearly',$sub_yearly_year)
            ->first();
        $yearly_paid_leave = $yearly_leave_count['added_paid_leave'];
        $sub_yearly_paid_leave = $sub_yearly_leave_count['added_paid_leave'];
        $annual_leave = $yearly_leave_count['added_annual_leave'];
        //履歴から使用日数を減算処理
        $compensatory_leave = 0;
        //画面生成
        $paid_leave = $yearly_paid_leave + $sub_yearly_paid_leave;
        $param['paid_leave'] = number_format($paid_leave,1);
        $param['yearly_paid_leave'] = number_format($yearly_paid_leave,1);
        $param['sub_yearly_paid_leave'] = number_format($sub_yearly_paid_leave,1);
        $param['compensatory_leave'] = number_format($compensatory_leave,1);
        $param['annual_leave'] = number_format($annual_leave,1);
        return view('paid_leave',$param);
    }

    /*
     * 現在の年度を返す
     * ※年度の始まりは４月とする
     * TODO:年度の始まりをどこかで指定できるようにする
     */
    private function get_yearly(){
        $now = new Carbon('now');
        $yearly = $now->subMonth(3);
        return $yearly->year;
    }
}
