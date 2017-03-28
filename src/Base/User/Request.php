<?php

namespace MCMIS\Foundation\Base\User;

use Illuminate\Support\Facades\Auth;
use MCMIS\Foundation\BaseRequest;

class Request extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $form = array();
        $form['name'] = 'required';
        $form['gender'] = 'required';
        if($this->method() != 'PUT') $form['avatar'] = 'required';
        if($this->method() != 'PUT') $form['agreement'] = 'required';
        $form['age'] = 'date_format:"m/d/Y"';
        if($this->method() != 'PUT') $form['password'] = 'required|confirmed|min:6';

        if($this->method() != 'PUT') $form['email'] = 'required|email|unique:users';

        $form['mobile'] = 'required|numeric|digits:10|unique:users,mobile'. ($this->method() ? ','. $this->input('editid') : '');

        $form['disability'] = 'required|boolean';
        $form['disability_type'] = 'required_if:disability,1';

        return $form;
    }
}
