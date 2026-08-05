<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fiscal_year_start_month',
        'paid_leave_auto_grant',
        'paid_leave_grant_days',
        'annual_leave_grant_days',
        'auto_grant_dismissed_fy',
    ];

    protected $casts = [
        'paid_leave_auto_grant' => 'boolean',
        'paid_leave_grant_days' => 'decimal:1',
        'annual_leave_grant_days' => 'decimal:1',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
