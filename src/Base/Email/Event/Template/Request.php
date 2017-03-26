<?php

namespace MCMIS\Foundation\Base\Email\Event\Template;

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
        return [
            'subject' => 'required',
            'body' => 'required'
        ];
    }
}
