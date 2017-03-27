<?php

namespace MCMIS\Foundation\Base\Complain\Assignment;

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
        $validation = ['department_id' => 'required|numeric'];

        if($this->has('employee_id')) $validation = ['employee_id' => 'required|numeric'];

        return $validation;
    }

    public function attributes(){
        return [
            'department_id' => 'department',
            'employee_id' => 'employee',
        ];
    }
}
