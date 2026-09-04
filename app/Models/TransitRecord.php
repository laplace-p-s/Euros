<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransitRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'use_date',
        'label',
        'route',
        'amount',
        'note',
        'destination_id',
    ];

    protected $casts = [
        'use_date' => 'date',
        'amount' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function destination()
    {
        return $this->belongsTo(TransitDestination::class, 'destination_id');
    }
}
