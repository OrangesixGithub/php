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

        $method = $request->route()->getActionMethod();
        $gates = config('acl.gates') ?? [];
        $permissionCheck = (empty($permission)
            ? (isset($gates[$method]) ? $gates[$method] : config('acl.gate_default'))
            : strtoupper($permission));

        if (enum_exists($acl) && defined($acl . "::{$permissionCheck}")) {
            Acl::acl(constant($acl . '::' . $permissionCheck)->value, true);
        } else {
            $message = config('app.debug')
                ? "Permissão não encontrada: {$acl}::{$permissionCheck}"
                : 'Acesso negado';
            abort(403, $message);
        }

        return $next($request);
    }
}
