<?php

namespace App\Libs;

use App\Models\Record;
use App\Models\Holiday;
use App\Models\Memo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Common
{
    static public function getTimeInfo(){
        $ret = array();
        $now = new Carbon('now');
        //出勤データ
        $records_attend = Record::where('user_id',Auth::id())
            ->where('method','1')
            ->whereDate('record_date', '=', $now->format('Y-m-d'))
            ->orderBy('record_date', 'desc')
            ->first();
        //退勤データ
        $records_leave = Record::where('user_id',Auth::id())
            ->where('method','2')
            ->whereDate('record_date', '=', $now->format('Y-m-d'))
            ->orderBy('record_date', 'desc')
            ->first();
        $s_datetime = is_null($records_attend) ? null : new Carbon($records_attend->record_date);
        $ret['s_time'] = is_null($s_datetime) ? '--:--:--' : $s_datetime->format('H:i:s');
        $e_datetime = is_null($records_leave) ? null : new Carbon($records_leave->record_date);
        $ret['e_time'] = is_null($e_datetime) ? '--:--:--' : $e_datetime->format('H:i:s');
        //勤務時間計算処理
        $ret['w_time'] = '- H';
        if (is_null($e_datetime)) $e_datetime = new Carbon('now'); //出勤時間から現在時刻を計算する
        if($s_datetime != null && $e_datetime != null){
            //分で出力し時間に変換、その後少数第二位で四捨五入
            $output_work_time = $s_datetime->diffInMinutes($e_datetime) / 60;
            if ($output_work_time > 4.0) $output_work_time = $output_work_time - 1; //休憩時間を加味し、4時間を超える場合は-1Hする
            $ret['w_time'] = round($output_work_time,1).' H';
        }
        return $ret;
    }

    static public function getSummaryInfo(Carbon $now,array $calendar){
        //集計
        $w_time_sum = 0;
        $w_day = 0;
        $w_day_h = 0;
        foreach ($calendar as $item){
            //w_time_sum : work_timeの合算
            //w_day      : weekが0か6またはholidayが1ではない時に1を加算、2h以上の場合は0.5を加算
            //w_day      : weekが0か6またはholidayが1の時に1を加算、2h以上の場合は0.5を加算
            if ($item['work_time'] != '-'){
                $w_time_str = str_replace(' H','',$item['work_time']);
                $w_time_sum = $w_time_sum + $w_time_str; //暗黙の型変換で数値処理される
                //集計加算数判定
                if($w_time_str > 6){
                    $add_day = 1;
                }else if ($w_time_str >= 2) {
                    $add_day = 0.5;
                }else{
                    $add_day = 0;
                }
                //平日休日判定
                if($item['week'] == 0 || $item['week'] == 6 || $item['holiday'] == 1){
                    $w_day_h = $w_day_h + $add_day;
                }else{
                    $w_day = $w_day + $add_day;
                }
            }
        }
        //表示用データセット
        $ret = array();
        $ret['now_disp'] = $now->format('Y年m月');
        $ret['now_announce'] = $now->format('Y/m/d H:i:s');
        $ret['work_time_sum'] = number_format(round($w_time_sum,1),1).' H';
        $ret['work_time_day'] = number_format(round($w_day,1),1).' day';
        $ret['work_time_day_h'] = number_format(round($w_day_h,1),1).' day';

        return $ret;
    }

    static public function generateCalendar(string $year,string $month){
        $ret = array();
        Carbon::setLocale('ja'); //効いてるのかわからないがおまじない的に記載
        $tgt_day = new Carbon($year.'-'.$month.'-01'); //初期値yyyy-MM-01
        for ($i=0; ;$i++){
            $ret[$i] = array(
                'date' => $tgt_day->isoFormat('YYYY/MM/DD (ddd)'),
                'date_format' => $tgt_day->isoFormat('YYYY-MM-DD'),
                'week' => $tgt_day->dayOfWeek, //日が0,土が6
                's_datetime' => '-',
                'e_datetime' => '-',
                'work_time' => '-',
                'memo' => '',
                'holiday' => 0,
                'holiday_name' => '',
                'is_today' => 0,
            );
            $tgt_day->addDay(1);
            if ($tgt_day->day == 1) break;
        }

        return $ret;
    }

    static public function setDatabaseData(array &$result_list,string $year,string $month){
        $base_date = new Carbon($year.'-'.$month.'-01');
        $base_date_end = $base_date->copy()->addMonthNoOverflow()->subDay();//一か月-1日=月末
        //出勤データリスト
        $records_attend = Record::where('user_id',Auth::id())
            ->where('method','1')
            ->whereDate('record_date', '>=', $base_date->format('Y-m-d'))
            ->whereDate('record_date', '<=', $base_date_end->format('Y-m-d'))
            ->orderBy('record_date')
            ->get();
        ;
        //退勤データリスト
        $records_leave = Record::where('user_id',Auth::id())
            ->where('method','2')
            ->whereDate('record_date', '>=', $base_date->format('Y-m-d'))
            ->whereDate('record_date', '<=', $base_date_end->format('Y-m-d'))
            ->orderBy('record_date')
            ->get();
        ;
        //出勤/退勤データセット処理
        foreach ($records_attend as $record){
            $tmp_date = new Carbon($record->record_date);
            $result_list[$tmp_date->day-1]['s_datetime'] = $tmp_date->format('H:i:s');
        }
        foreach ($records_leave as $record){
            $tmp_date = new Carbon($record->record_date);
            $result_list[$tmp_date->day-1]['e_datetime'] = $tmp_date->format('H:i:s');
        }
        //勤務時間計算処理 - 日付をまたぐものはひとまず考えないものとする
        $count = 0;
        foreach ($result_list as $result){
            if($result['s_datetime'] != '-' && $result['e_datetime'] != '-'){
                $s_date = new Carbon('2020-01-10 '.$result['s_datetime']);
                $e_date = new Carbon('2020-01-10 '.$result['e_datetime']);
                //分で出力し時間に変換、その後少数第二位で四捨五入
                $output_work_time = $s_date->diffInMinutes($e_date) / 60;
                if ($output_work_time > 4.0) $output_work_time = $output_work_time - 1; //休憩時間を加味し、4時間を超える場合は-1Hする
                $result_list[$count]['work_time'] = round($output_work_time,1).' H';
            }
            $count++;
        }
        //メモデータリスト処理
        $memos = Memo::where('user_id',Auth::id())
            ->whereDate('record_date', '>=', $base_date->format('Y-m-d'))
            ->whereDate('record_date', '<=', $base_date_end->format('Y-m-d'))
            ->orderBy('record_date')
            ->get();
        if(!is_null($memos)){
            foreach ($memos as $memo){
                $tmp_date = new Carbon($memo->record_date);
                $result_list[$tmp_date->day-1]['memo'] = $memo->memo;
            }
        }
        //休日設定処理
        $holidays = Holiday::where('user_id',Auth::id())
            ->whereDate('holiday_date', '>=', $base_date->format('Y-m-d'))
            ->whereDate('holiday_date', '<=', $base_date_end->format('Y-m-d'))
            ->orderBy('holiday_date')
            ->get();
        if(!is_null($holidays)){
            foreach ($holidays as $holiday){
                $tmp_date = new Carbon($holiday->holiday_date);
                $result_list[$tmp_date->day-1]['holiday'] = 1;
                if (!is_null($holiday->name)) $result_list[$tmp_date->day-1]['holiday_name'] = $holiday->name;
            }
        }
        //today設定処理
        $today = Carbon::now();
        foreach ($result_list as $result){
            if($result['date_format'] == $today->isoFormat('YYYY-MM-DD')){
                $result_list[$today->day-1]['is_today'] = 1;
                break;
            }
        }
    }
}
