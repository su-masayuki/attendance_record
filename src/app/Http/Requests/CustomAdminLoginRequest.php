<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomAdminLoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => '正しい形式のメールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }
}