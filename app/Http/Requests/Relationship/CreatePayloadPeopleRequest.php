<?php

namespace App\Http\Requests\Relationship;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreatePayloadPeopleRequest extends BaseRequest
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
            'name'=>'required|min:3',
            'tag'=>'required|string',
            'first_meeting'=>'required',
            'email'=>'required|email',
            'phone'=>'required'
        ];
    }
}
