<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransitDestination extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'route',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'integer',
        'sort_order' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transitRecords()
    {
        return $this->hasMany(TransitRecord::class, 'destination_id');
    }
}
