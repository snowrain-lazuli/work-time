<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{

    use HasFactory;
    protected $fillable = [
        'time_id',
        'applicant_id',
        'approver_id',
        'day',
        'application_type',
        'status'
    ];

    protected $guarded = [
        'id',
    ];

    //リレーションの設定
    public function time()
    {
        return $this->belongsTo(Time::class);
    }
    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    // 承認者（Userとのリレーション）
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}