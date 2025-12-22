<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Holiday;
use App\Models\HolidayTemplate;

class SettingsController extends Controller
{
    public function index(Request $request){
        return view('settings_top');
    }

    public function holiday_show(Request $request){
        //リクエスト時の年を取得
        $now = new Carbon('now');
        $now_year = $now->year;
        //結果リストを取得
        $result_list = $this->get_holiday_list();
        //画面生成
        $param = compact('result_list','now_year');
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
