<?php

namespace Orangesix\Acl\Http\Middleware;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Orangesix\Acl\Facades\Acl;

class AclMiddleware
{
    /**
     *  Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws BindingResolutionException
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): mixed
    {
        $controller = get_class($request->route()->getController());
        $acl = str_replace(['App\\Http\\Controllers\\', 'Controller'], ['App\\Acl\\', 'Acl'], $controller);

        $permissionCheck = (empty($permission) ? 'VISUALIZAR' : strtoupper($permission));
        if (enum_exists($acl) && defined($acl . "::{$permissionCheck}")) {
            Acl::acl(constant($acl . '::' . $permissionCheck)->value, true);
        }

        return $next($request);
    }
}
