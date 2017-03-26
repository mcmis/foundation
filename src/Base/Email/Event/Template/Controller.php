<?php

namespace MCMIS\Foundation\Base\Email\Event\Template;

use MCMIS\Foundation\BaseController;

class Controller extends BaseController
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index(){
        $items = sys('model.email.event.template')->paginate(15);
        return view('layout::email.event.template.list', ['items' => $items]);
    }

    public function edit($event){
        $item = sys('model.email.event.template')->where('event_alias', '=', $event)->first();

        return view('layout::email.event.template.edit', ['item' => $item]);
    }

    public function update(Request $request, $event){
        $item = sys('model.email.event.template')->where('event_alias', '=', $event)->update($request->only(['subject', 'body']));
        if($item)
            flash()->success(trans('alert.email.template.updated', ['subject' => $request->subject, 'event' => $event]));
        else flash()->error(trans('alert.email.template.update.fail', ['event' => $event]));

        return redirect()->route('email.event.templates');
    }
}
