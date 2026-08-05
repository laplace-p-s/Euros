<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\LeaveController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard',function (){return redirect('/home');}); //エラー防止 - 旧ルートの転送措置
Route::get('/home', [HomeController::class, 'show'])->middleware(['auth', 'verified'])->name('home');
Route::get('/search', [SearchController::class, 'show'])->middleware(['auth', 'verified'])->name('search');
Route::post('/search', [SearchController::class, 'search'])->middleware(['auth', 'verified'])->name('search.post');
Route::get('/detail', function (){return redirect('/home');}); //エラー防止
Route::post('/detail', [SearchController::class, 'detailPost'])->middleware(['auth', 'verified'])->name('detail');
Route::post('/add_record', [SearchController::class, 'addRecord'])->middleware(['auth', 'verified'])->name('detail.add_record');

//設定
Route::get('/settings', [SettingsController::class, 'index'])->middleware(['auth', 'verified'])->name('settings.index');
Route::get('/settings/holiday', [SettingsController::class, 'holiday_show'])->middleware(['auth', 'verified'])->name('settings.holiday');
Route::post('/settings/holiday', [SettingsController::class, 'holiday_add'])->middleware(['auth', 'verified'])->name('settings.holiday_add');
Route::post('/settings/holiday/template', [SettingsController::class, 'holiday_template_add'])->middleware(['auth', 'verified'])->name('settings.holiday_template_add');
Route::get('/settings/holiday/template_preview', [SettingsController::class, 'holiday_template_preview'])->middleware(['auth', 'verified'])->name('settings.holiday_template_preview');
Route::post('/settings/holiday/delete', [ApiController::class, 'holidayDelete'])->middleware(['auth', 'verified'])->name('settings.holiday_del');

//設定 - 基本設定
Route::get('/settings/general', [SettingsController::class, 'generalShow'])->middleware(['auth', 'verified'])->name('settings.general');
Route::post('/settings/general', [SettingsController::class, 'generalSave'])->middleware(['auth', 'verified'])->name('settings.general_save');

//ツール
Route::get('/tools', [ToolsController::class, 'index'])->middleware(['auth', 'verified'])->name('tools');

//有給管理
Route::get('/leave', [LeaveController::class, 'index'])->middleware(['auth', 'verified'])->name('leave');
Route::post('/leave/auto_grant', [LeaveController::class, 'executeAutoGrant'])->middleware(['auth', 'verified'])->name('leave.auto_grant');
Route::post('/leave/auto_grant/dismiss', [LeaveController::class, 'dismissAutoGrant'])->middleware(['auth', 'verified'])->name('leave.auto_grant.dismiss');
Route::post('/leave/usage', [LeaveController::class, 'addUsage'])->middleware(['auth', 'verified'])->name('leave.usage.add');
Route::post('/leave/usage/delete', [LeaveController::class, 'deleteUsage'])->middleware(['auth', 'verified'])->name('leave.usage.delete');
Route::post('/leave/grant', [LeaveController::class, 'addGrant'])->middleware(['auth', 'verified'])->name('leave.grant.add');
Route::post('/leave/grant/delete', [LeaveController::class, 'deleteGrant'])->middleware(['auth', 'verified'])->name('leave.grant.delete');

//API
Route::post('/register_rec', [ApiController::class, 'register'])->middleware(['auth', 'verified'])->name('register_rec');
Route::post('/memo_edit', [ApiController::class, 'memoEdit'])->middleware(['auth', 'verified'])->name('memo_edit');
Route::post('/renewal_info', [ApiController::class, 'renewalInfo'])->middleware(['auth', 'verified'])->name('renewal_info');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
