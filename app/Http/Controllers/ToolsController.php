<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $yearly_paid_leave = $yearly_leave_count['added_paid_leave']; //今年度配布有給
        $sub_yearly_paid_leave = $sub_yearly_leave_count['added_paid_leave']; //昨年度配布有給
        $annual_leave = $yearly_leave_count['added_annual_leave']; //年度休暇
        //履歴から使用日数を減算処理
        // 昨年度の合計有給使用量を集計し、昨年度の数値から差し引く
        $sub_paid_leave_sum_obj = DB::table('leave_histories')
            ->selectRaw('SUM(leave_amount) as sub_paid_leave_sum')
            ->where('user_id',Auth::id())
            ->where('yearly',$sub_yearly_year)
            ->where('leave_class','1')
            ->groupBy('yearly')
            ->get();
        //TODO:データが0件だと落ちる
        $sub_paid_leave_sum = $sub_paid_leave_sum_obj[0]->sub_paid_leave_sum; //昨年度使用有給合計
        $sub_yearly_paid_leave = $sub_yearly_paid_leave - $sub_paid_leave_sum; //計算
        // 今年度の合計有給使用量を集計し、昨年度の数値から差し引く　マイナスになった場合は今年度の数値から差し引く
        $paid_leave_sum_obj = DB::table('leave_histories')
            ->selectRaw('SUM(leave_amount) as paid_leave_sum')
            ->where('user_id',Auth::id())
            ->where('yearly',$yearly_year)
            ->where('leave_class','1')
            ->groupBy('yearly')
            ->get();
        //TODO:データが0件だと落ちる
        $paid_leave_sum = $paid_leave_sum_obj[0]->paid_leave_sum; //昨年度使用有給合計
        $sub_yearly_paid_leave = $sub_yearly_paid_leave - $paid_leave_sum; //計算1 昨年度-今年度計
        if($sub_yearly_paid_leave < 0){
            $yearly_paid_leave = $yearly_paid_leave - $sub_yearly_paid_leave; //計算2 今年度-昨年度余剰分
            $sub_yearly_paid_leave = 0.0;
        }
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
