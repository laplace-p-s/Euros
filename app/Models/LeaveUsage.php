<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'usage_date',
        'days',
        'note',
        'record_id',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'days' => 'decimal:1',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function record()
    {
        return $this->belongsTo(Record::class);
    }
}
