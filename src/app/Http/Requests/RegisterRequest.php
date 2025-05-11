<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required | unique:users',
            'email' => 'required | email | unique:users',
            'password' => 'required | min:8 | confirmed',
            'password_confirmation' => 'required | min:8 | '
        ];
    }
    /**
     *  バリデーション項目名定義
     * @return array
     */

    public function messages()
    {
        return [
            'name.required' => 'お名前を入力して下さい。',
            'name.unique' => 'その名前はすでに使用されています。',
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスはメール形式で入力してください',
            'email.unique' => 'そのメールアドレスはすでに使用されています',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password_confirmation.required' => '確認用パスワードを入力してください',
            'password_confirmation.min' => '確認用パスワードは8文字以上で入力してください',
            'password_confirmation.confirmed' => 'パスワードと一致しません'
        ];
    }
}