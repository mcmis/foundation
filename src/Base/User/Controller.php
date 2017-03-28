<?php

namespace MCMIS\Foundation\Base\User;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MCMIS\Foundation\BaseController;
use MCMIS\Foundation\Base\User\Request as UserRequest;

class Controller extends BaseController
{

    public function registerUser(UserRequest $request){
        $user = sys('model.user')->create($request->input());
        //attach avatar with user profile
        if($user) {
            if ($request->has('avatar'))
                $user->avatar()->attach($request->input('avatar'));
            flash()->success(trans('alert.user.registered', ['name' => $request->name, 'email' => $request->email, 'mobile' => $request->mobile]));
        }else flash()->error(trans('alert.user.register.fail'));

        return $user;
    }

    public function search(Request $keyword){
        $result = $this->searchUser($keyword);
        if(!$result)
            return redirect()->back()->withErrors(trans('alert.user.not_found'), 'user')->withInput();

        return redirect()->route('profile', $result->first()->id);
        //below line is for redirecting to complain form
        //Redirect::action('ComplainsController@create', ['user' => $result->first()->id]);
    }

    public function searchForComplain(Request $keyword){
        $result = $this->searchUser($keyword);
        if(!$result)
            return redirect()->route('profile.create', ['suggest' => $keyword->input('keyword')]);

        return redirect()->route('complain.create', $result->first()->id);
    }

    public function ajaxSearch(Request $keyword){
        $result = $this->searchUser($keyword);
        if(!$result)
            return response()->json(['success' => false, 'errors' => trans('alert.user.not_found'), 'query' => $keyword->input('keyword')], 400);

        return response()->json(['success' => $result->first()], 200);
    }

    /* Search Method */
    protected function searchUser(Request $keyword){
        if(!Auth::user()->hasRole('operator') && !Auth::user()->hasRole('admin') && !Auth::user()->hasRole('supervisor')) return false;
        $search_col =  filter_var($keyword->input('keyword'), FILTER_VALIDATE_EMAIL)? 'email' : 'mobile';

        $model = sys('model.user')->where($search_col, '=', $keyword->input('keyword'))->get();
        if($model->isEmpty())
            return false;

        return $model;
    }
}
