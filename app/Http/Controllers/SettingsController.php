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
        $now = Carbon::now();
        $selected_year = (int)$request->input('year', $now->year);

        // 年リスト生成（登録済み年 + テンプレート年 + 現在年を合算）
        $registered_years = Holiday::where('user_id', Auth::id())
            ->selectRaw('YEAR(holiday_date) as y')
            ->distinct()
            ->pluck('y')
            ->toArray();
        $template_years = HolidayTemplate::select('year')
            ->distinct()
            ->pluck('year')
            ->toArray();
        $all_years = array_unique(array_merge($registered_years, $template_years, [$now->year]));
        rsort($all_years);
        $year_list = $all_years;

        $result_list = $this->get_holiday_list($selected_year);
        $has_template = HolidayTemplate::where('year', $selected_year)->exists();
        $param = compact('result_list', 'year_list', 'selected_year', 'has_template');
        return view('settings_holiday', $param);
    }

    public function holiday_add(Request $request){
        $param = $request->all();
        $year = $request->input('selected_year', Carbon::now()->year);

        $holiday = new Holiday();
        $holiday->user_id = Auth::id();
        $holiday->holiday_date = $param['date'];
        $holiday->name = $param['name'];
        $holiday->note = $param['note'] ?? null;
        $holiday->save();

        return redirect()->route('settings.holiday', ['year' => $year])
            ->with('message', '祝祭日を追加しました');
    }

    public function holiday_template_add(Request $request){
        $year = (int)$request->input('year', Carbon::now()->year);
        $this->add_holiday_from_template($year);

        return redirect()->route('settings.holiday', ['year' => $year])
            ->with('message', 'テンプレートから祝祭日を追加しました');
    }

    public function holiday_template_preview(Request $request){
        $year = (int)$request->input('year', Carbon::now()->year);
        $templates = HolidayTemplate::where('year', $year)
            ->orderBy('holiday_date')
            ->get();

        $ret = array();
        foreach ($templates as $t){
            $date = new Carbon($t->holiday_date);
            $ret[] = array(
                'date' => $date->isoFormat('YYYY/MM/DD (ddd)'),
                'name' => $t->name,
            );
        }
        return response()->json($ret);
    }

    private function get_holiday_list($year = null){
        $ret_array = array();
        $query = Holiday::where('user_id', Auth::id());
        if ($year) {
            $query->whereYear('holiday_date', $year);
        }
        $holiday_list = $query->orderBy('holiday_date')->get();

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
            'show_expired_stock' => 'required|boolean',
            'show_compensatory' => 'required|boolean',
            'compensatory_hide_zero' => 'required|boolean',
            'annual_leave_grant_days' => 'required|numeric|min:0',
        ]);

        $leaveService = new LeaveService();
        $settings = $leaveService->getOrCreateSettings(Auth::id());
        $settings->fiscal_year_start_month = $request->input('fiscal_year_start_month');
        $settings->paid_leave_auto_grant = $request->input('paid_leave_auto_grant');
        $settings->paid_leave_grant_days = $request->input('paid_leave_grant_days');
        $settings->show_expired_stock = $request->input('show_expired_stock');
        $settings->show_compensatory = $request->input('show_compensatory');
        $settings->compensatory_hide_zero = $request->input('compensatory_hide_zero');
        $settings->annual_leave_grant_days = $request->input('annual_leave_grant_days');
        $settings->save();

        return redirect()->route('settings.general')->with('message', '設定を保存しました');
    }

    private function add_holiday_from_template($year = null){
        if ($year === null) {
            $year = Carbon::now()->year;
        }
        $templates = HolidayTemplate::where('year', $year)
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
