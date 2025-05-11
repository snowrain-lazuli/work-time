<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{

    use HasFactory;
    protected $fillable = [
        'user_id',
        'time_id',
        'start_time',
        'end_time',
    ];

    protected $guarded = [
        'id',
    ];

    //リレーションの設定
    public function time()
    {
        return $this->belongsTo(Time::class);
    }
}