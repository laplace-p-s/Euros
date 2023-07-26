<?php

namespace App\Http\Controllers;

use App\Libs\Common;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Record;
use App\Models\Memo;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function register(Request $request)
    {
        $ret = array();
        $method = $request->input('method');
        // 格納データのセット
        $record = new Record();
        $record->user_id = Auth::id();
        $record->record_date = Carbon::now();
        $record->method = $method;
        // 格納データの保存
        //TODO:エラー制御の実装
        $record->save();
        // レスポンスの作成
        $method_txt = $method == 1 ? '出勤' : '退勤';
        $ret['message'] = $method_txt.'登録が完了しました。('.$record->record_date->format('Y/m/d H:i:s').')';

        return response()->json($ret);
    }

    public function memoEdit(Request $request)
    {
        $ret = array();
        $date = $request->input('edit_date');
        $new_memo = $request->input('memo');
        // 格納データの検索
        $memo = Memo::where('user_id',Auth::id())
            ->where('record_date',$date)
            ->first();
        // 格納データのセット
        if(is_null($memo)){
            $memo = new Memo();
        }
        $memo->user_id = Auth::id();
        $memo->record_date = $date;
        $memo->memo = $new_memo ?? '';
        // 格納データの保存
        //TODO:エラー制御の実装
        $memo->save();
        // レスポンスの作成
        $ret['message'] = '処理が完了しました。';

        return response()->json($ret);
    }

    public function holidayDelete(Request $request)
    {
        $ret = array();
        $num = $request->input('num'); //削除指定ID
        // 格納データの検索
        $holiday = Holiday::where('user_id',Auth::id())
            ->where('id',$num)
            ->first();
        if(!is_null($holiday)){
            //削除
            Holiday::destroy($num);
            // レスポンスの作成
            $ret['message'] = '処理が完了しました。';
        }else{
            // レスポンスの作成
            $ret['message'] = 'エラー';
        }
        return response()->json($ret);
    }

    public function renewalInfo(Request $request){
        $ret = Common::getTimeInfo();
        return response()->json($ret);
    }
}
