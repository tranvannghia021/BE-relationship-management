<?php

namespace App\Http\Requests\Account;

use App\Http\Requests\BaseRequest;
use App\Traits\Response;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends BaseRequest
{
    use Response;
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
            'name'=>'required|string|max:80',
            'email'=>'required|email',
            'password'=>'required|min:8',
            'confirm_password'=>'required|min:8',
            'phone_number'=>'required|min:10|regex:/^([0-9\s\-\+\(\)]*)$/'
        ];
    }
}
