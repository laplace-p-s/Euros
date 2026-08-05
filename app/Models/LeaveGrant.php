<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveGrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'fiscal_year',
        'grant_days',
        'effective_date',
        'expiry_date',
        'is_auto',
        'note',
    ];

    protected $casts = [
        'grant_days' => 'decimal:1',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'is_auto' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
