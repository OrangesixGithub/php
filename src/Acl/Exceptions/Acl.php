<?php

namespace Orangesix\Acl\Exceptions;

use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Session;

class Acl extends \Exception
{
    /**
     * @return Application|Redirector|string|RedirectResponse|\Illuminate\Contracts\Foundation\Application
     */
    public function render(): Application|Redirector|string|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        if (request()->method() == 'GET') {
            Session::flash('message', $this->message);
            Session::flash('messageType', 'warning');
            return redirect(url()->previous());
        } else {
            abort(400, $this->message);
        }
    }
}
