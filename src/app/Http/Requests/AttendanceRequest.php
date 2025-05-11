<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\MessageBag;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'work_start' => ['required', 'date_format:H:i'],
            'work_end' => ['required', 'date_format:H:i'],
            'detail' => ['required'],
            'breaks.*.start_time' => ['nullable', 'date_format:H:i'],
            'breaks.*.end_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages()
    {
        return [
            'work_start.required' => '', // 空にしておく（後で統合表示）
            'work_end.required' => '',
            'work_start.date_format' => '',
            'work_end.date_format' => '',
            'breaks.*.start_time.date_format' => '',
            'breaks.*.end_time.date_format' => '',
            'detail.required' => '備考を記入してください',
        ];
    }
    public function withValidator($validator)
    {
        $breaks = $this->input('breaks', []);
        $hasWorkError = false;

        // newの処理（空・スペースなら削除、endのみ入力なら削除＋エラー）
        if (isset($breaks['new'])) {
            $newStart = trim($breaks['new']['start_time'] ?? '');
            $newEnd = trim($breaks['new']['end_time'] ?? '');

            if ($newStart === '' && $newEnd === '') {
                unset($breaks['new']);
            } elseif ($newEnd !== '' && $newStart === '') {
                unset($breaks['new']);
                $hasWorkError = true;
            }
        }

        // newの処理結果を反映
        $this->merge(['breaks' => $breaks]);

        $validator->after(function ($validator) use (&$hasWorkError) {
            $start = $this->input('work_start');
            $end = $this->input('work_end');
            $breaks = $this->input('breaks', []);
            $messages = new MessageBag();

            // 出勤・退勤のバリデーション
            if (!$this->filled('work_start') || !$this->filled('work_end')) {
                $hasWorkError = true;
            } elseif (strtotime($start) >= strtotime($end)) {
                $hasWorkError = true;
            }

            // 休憩のバリデーション（整合性チェック）
            $parsedBreaks = [];

            foreach ($breaks as $break) {
                $bStart = $break['start_time'] ?? null;
                $bEnd = $break['end_time'] ?? null;

                if ($bStart || $bEnd) {
                    if (!$bStart || !$bEnd || strtotime($bStart) >= strtotime($bEnd)) {
                        $hasWorkError = true;
                        continue;
                    }
                    if (strtotime($bStart) < strtotime($start) || strtotime($bEnd) > strtotime($end)) {
                        $hasWorkError = true;
                        continue;
                    }

                    // 重複チェック用に配列へ追加
                    $parsedBreaks[] = [
                        'start' => strtotime($bStart),
                        'end' => strtotime($bEnd),
                    ];
                }
            }

            // 重複チェック
            for ($i = 0; $i < count($parsedBreaks); $i++) {
                for ($j = $i + 1; $j < count($parsedBreaks); $j++) {
                    $a = $parsedBreaks[$i];
                    $b = $parsedBreaks[$j];
                    if ($a['start'] < $b['end'] && $b['start'] < $a['end']) {
                        $hasWorkError = true;
                        break 2; // 1回のメッセージだけ表示するため、即終了
                    }
                }
            }

            // エラーメッセージ（1回だけ）
            if ($hasWorkError) {
                $messages->add('work_time', '出勤時間もしくは退勤時間が不適切な値です');
                $validator->messages()->merge($messages);
            }
        });
    }
}