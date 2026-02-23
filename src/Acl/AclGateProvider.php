<?php

namespace Orangesix\Acl;

use Illuminate\Http\Request;

class AclGateProvider
{
    /**
     * @param Request $request
     * @return string
     */
    public static function resolve(Request $request): string
    {
        return empty($request->acl)
            ? config('acl.gate_default')
            : $request->acl;
    }
}
