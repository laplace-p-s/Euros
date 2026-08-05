<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Holiday;
use App\Models\HolidayTemplate;
use App\Models\LeaveSetting;
use App\Services\LeaveService;

class SettingsController extends Controller
{
    public function index(Request $request){
        return view('settings_top');
    }

    public function holiday_show(Request $request){
        //画面生成
        $result_list = $this->get_holiday_list();
        $param = compact('result_list');
        return view('settings_holiday',$param);
    }

    public function holiday_add(Request $request){
        //パラメータの取得
        $param = $request->all();
        if ($param['action'] == 'template_add'){
            $this->add_holiday_from_template();
        }else{
            //データセット
            $holiday = new Holiday();
            $holiday->user_id = Auth::id();
            $holiday->holiday_date = $param['date'];
            $holiday->name = $param['name'];
            $holiday->note = $param['note'];
            $holiday->save();
        }
        //画面生成
        $result_list = $this->get_holiday_list();
        $param = compact('result_list');
        return view('settings_holiday',$param);
    }

    private function get_holiday_list(){
        $ret_array = array();
        $holiday_list = Holiday::where('user_id',Auth::id())
            ->orderBy('holiday_date')
            ->get();
        $c = 0;
        foreach ($holiday_list as $item){
            $date = new Carbon($item->holiday_date);
            $ret_array[$c] = array(
                'num' => $item->id,
                'date' => $date->isoFormat('YYYY/MM/DD (ddd)'),
                'name' => $item->name,
                'note' => $item->note,
            );
            $c++;
        }

        return $ret_array;
    }

    /**
     * 基本設定画面表示
     */
    public function generalShow(Request $request){
        $leaveService = new LeaveService();
        $settings = $leaveService->getOrCreateSettings(Auth::id());
        $param = compact('settings');
        return view('settings_general', $param);
    }

    /**
     * 基本設定保存
     */
    public function generalSave(Request $request){
        $request->validate([
            'fiscal_year_start_month' => 'required|integer|min:1|max:12',
            'paid_leave_auto_grant' => 'required|boolean',
            'paid_leave_grant_days' => 'required|numeric|min:0',
            'annual_leave_grant_days' => 'required|numeric|min:0',
        ]);

        $leaveService = new LeaveService();
        $settings = $leaveService->getOrCreateSettings(Auth::id());
        $settings->fiscal_year_start_month = $request->input('fiscal_year_start_month');
        $settings->paid_leave_auto_grant = $request->input('paid_leave_auto_grant');
        $settings->paid_leave_grant_days = $request->input('paid_leave_grant_days');
        $settings->annual_leave_grant_days = $request->input('annual_leave_grant_days');
        $settings->save();

        return redirect()->route('settings.general')->with('message', '設定を保存しました');
    }

    private function add_holiday_from_template(){
        $now = Carbon::now();
        $now_year = $now->year;
        $templates = HolidayTemplate::where('year',$now_year)
            ->orderBy('holiday_date')
            ->get();
        $data = array();
        $c = 0;
        foreach ($templates as $template){
            $data[$c] = array(
                'user_id' => Auth::id(),
                'holiday_date' => $template->holiday_date,
                'name' => $template->name,
                'note' => $template->note
            );
            $c++;
        }
        Holiday::insert($data);
    }
}
