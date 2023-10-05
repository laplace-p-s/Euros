<?php

namespace App\Http\Controllers;

use App\Libs\Common;
use App\Models\Holiday;
use App\Models\Memo;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Record;

class SearchController extends Controller
{
    public function show(Request $request){
        //ダミーの変数を生成
//        $result_list = array();
        $tmp_carbon = new Carbon('now');
        $selected_year = $tmp_carbon->year;
        $selected_month = $tmp_carbon->month;
        //現在年月の検索を画面描画時に実施
        $result_list = Common::generateCalendar($selected_year,$selected_month);
        Common::setDatabaseData($result_list,$selected_year,$selected_month);
        //画面生成用の変数をセット
        $year_list = $this->setupSelectItemYear();
        $month_list = $this->setupSelectItemMonth();
        $param = compact('result_list','year_list','month_list','selected_year','selected_month');
        return view('search',$param);
    }

    public function search(Request $request){
        //パラメータの取得
        $param = $request->all();
        //前月|翌月遷移処理
        if ($param['action'] == 'back' || $param['action'] == 'next'){
            //paramのyearとmonthを書き換える
            $action_num = 0;
            if ($param['action'] == 'back') $action_num = 1;
            if ($param['action'] == 'next') $action_num = 2;
            $this->switchParamYearMonth($action_num,$param);
        }
        //カレンダーを作成
        $result_list = Common::generateCalendar($param['year'],$param['month']);
        //DBから情報取得し画面表示用に加工
        Common::setDatabaseData($result_list,$param['year'],$param['month']);
        //画面生成用の変数をセット
        $year_list = $this->setupSelectItemYear();
        $month_list = $this->setupSelectItemMonth();
        $selected_year = $param['year'];
        $selected_month = $param['month'];

        $param = compact('result_list','year_list','month_list','selected_year','selected_month');
        return view('search',$param);
    }

    public function detailPost(Request $request){
        //TODO:検索画面で同じ計算処理をしている部分があるため、共通処理化する
        //パラメータの取得
        $param = $request->all();
        if ($param['action'] == 'delete'){
            //削除の処理
            $num = $param['num']; //削除指定ID
            //データの検索
            $record = Record::where('user_id',Auth::id())
                ->where('id',$num)
                ->first();
            if(!is_null($record)){
                //削除
                Record::destroy($num);
            }
        }
        $tgt_date = new Carbon($param['tgt_date']);
        //画面描画情報の収集
        //出勤データリスト
        $records_attend = Record::where('user_id',Auth::id())
            ->where('method','1')
            ->whereDate('record_date', '=', $tgt_date->format('Y-m-d'))
            ->orderBy('record_date')
            ->get();
        //退勤データリスト
        $records_leave = Record::where('user_id',Auth::id())
            ->where('method','2')
            ->whereDate('record_date', '=', $tgt_date->format('Y-m-d'))
            ->orderBy('record_date')
            ->get();
        //出勤/退勤データセット処理
        $s_datetime = '-';
        $e_datetime = '-';
        foreach ($records_attend as &$record){
            $tmp_date = new Carbon($record->record_date);
            $s_datetime = $tmp_date->format('H:i:s');
            $record->record_date = $tmp_date->isoFormat('YYYY/MM/DD (ddd) HH:mm:ss');
        }
        unset($record); //参照渡ししたforeachのバグ回避用
        foreach ($records_leave as &$record){
            $tmp_date = new Carbon($record->record_date);
            $e_datetime = $tmp_date->format('H:i:s');
            $record->record_date = $tmp_date->isoFormat('YYYY/MM/DD (ddd) HH:mm:ss');
        }
        unset($record); //参照渡ししたforeachのバグ回避用
        //勤務時間計算処理 - 日付をまたぐものはひとまず考えないものとする
        $work_time = '-';
        if($s_datetime != '-' && $e_datetime != '-'){
            $s_date = new Carbon('2020-01-10 '.$s_datetime);
            $e_date = new Carbon('2020-01-10 '.$e_datetime);
            //分で出力し時間に変換、その後少数第二位で四捨五入
            $output_work_time = $s_date->diffInMinutes($e_date) / 60;
            if ($output_work_time > 4.0) $output_work_time = $output_work_time - 1; //休憩時間を加味し、4時間を超える場合は-1Hする
            $work_time = round($output_work_time,1).' H';
        }
        //メモデータリスト処理
        $memo = '';
        $memos = Memo::where('user_id',Auth::id())
            ->whereDate('record_date', '=', $tgt_date->format('Y-m-d'))
            ->orderBy('record_date')
            ->get();
        if(!is_null($memos)){
            foreach ($memos as $mem){
                $memo = $mem->memo;
            }
        }
        //画面描画情報の生成
        $detail_data = array();
        $detail_data['date']       = $tgt_date->isoFormat('YYYY/MM/DD (ddd)'); //日付
        $detail_data['date_orig']  = $tgt_date->isoFormat('YYYY-MM-DD');
        $detail_data['s_datetime'] = $s_datetime; //出勤時間
        $detail_data['e_datetime'] = $e_datetime; //退勤時間
        $detail_data['work_time']  = $work_time; //勤務時間
        $detail_data['memo']       = $memo; //メモ
        $detail_data['attend_count'] = count($records_attend);
        $detail_data['leave_count'] = count($records_leave);
        //dd($param);

        $param = compact('detail_data','tgt_date','records_attend','records_leave');
        return view('detail',$param);
    }

    public function addRecord(Request $request){
        //dd($request);
        //パラメータの取得
        $param = $request->all();
        $action = array_key_exists('action',$param) ? $param['action'] : null;
        if (!is_null($action)){
            // 格納データのセット
            $record = new Record();
            $record->user_id = Auth::id();
            $record->record_date = new Carbon($param['record-date'].' '.$param['record-time']);
            $record->method = $param['record-method'];
            $record->is_manual = 1;
            // 格納データの保存
            //TODO:エラー制御の実装
            $record->save();

            return redirect('search');
        }
    }

    private function setupSelectItemYear(){
        //configから開始年を取得(初期値2022)、そこから現在年+1年分の選択肢を生成
        $now = new Carbon('now');
        $ret = array();
        $start_year = config('euros.StartSelectYear');
        $current_year = $now->year;
        for($i = $start_year; $i != $current_year + 2; $i++){
            $ret[$i] = $i.'年';
        }
        return $ret;
    }

    private function switchParamYearMonth($action_num, &$param){
        //action 1:back, 2:next
        $date = new Carbon($param['year'].'-'.$param['month'].'-01');
        if ($action_num == 1){
            $date->addMonth(-1);
        }elseif ($action_num == 2){
            $date->addMonth(1);
        }else{
            //none
        }
        $param['year'] = $date->year;
        $param['month'] = $date->month;
    }

    private function setupSelectItemMonth(){
        return [
            '1'  => '1月',
            '2'  => '2月',
            '3'  => '3月',
            '4'  => '4月',
            '5'  => '5月',
            '6'  => '6月',
            '7'  => '7月',
            '8'  => '8月',
            '9'  => '9月',
            '10' => '10月',
            '11' => '11月',
            '12' => '12月',
        ];
    }
}
