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

    // paid_leave_show は LeaveController に移行済み
}
