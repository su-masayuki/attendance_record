<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true; // 認証済ユーザーなら誰でも許可
    }

    public function rules()
{
    return [
        'clock_in' => ['required', 'date_format:H:i'],
        'clock_out' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) {
            $in = $this->input('clock_in');
            if ($in && $value && $in > $value) {
                $fail('出勤時間もしくは退勤時間が不適切な値です');
            }
        }],
        'breaks.*.start' => ['nullable', 'date_format:H:i', function ($attribute, $value, $fail) {
            $in = $this->input('clock_in');
            $out = $this->input('clock_out');
            if ($value && ($value < $in || $value > $out)) {
                $fail('休憩時間が勤務時間外です');
            }
        }],
        'breaks.*.end' => ['nullable', 'date_format:H:i', function ($attribute, $value, $fail) {
            $in = $this->input('clock_in');
            $out = $this->input('clock_out');
            if ($value && ($value < $in || $value > $out)) {
                $fail('休憩時間が勤務時間外です');
            }
        }],
        'note' => ['required', 'string'],
    ];
}

public function messages()
{
    return [
        'note.required' => '備考を記入してください',
        'clock_in.required' => '出勤時間を入力してください',
        'clock_out.required' => '退勤時間を入力してください',
        'clock_in.date_format' => '出勤時間の形式が不正です',
        'clock_out.date_format' => '退勤時間の形式が不正です',
        'breaks.*.start.date_format' => '休憩開始時間の形式が不正です',
        'breaks.*.end.date_format' => '休憩終了時間の形式が不正です',
    ];
}
}