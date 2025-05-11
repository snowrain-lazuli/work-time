<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Time extends Model
{

    use HasFactory;
    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'status'
    ];

    protected $guarded = [
        'id',
    ];

    //リレーションの設定
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }
    public function application()
    {
        return $this->hasMany(Application::class);
    }
}