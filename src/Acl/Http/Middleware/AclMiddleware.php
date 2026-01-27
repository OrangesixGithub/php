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
        $gateRule = !empty($permission)
            ? strtoupper($permission)
            : (isset($gates[$method]) ? $gates[$method] : config('acl.gate_default'));

        if ($gateRule instanceof Closure) {
            $permissionCheck = $gateRule($request);
        } else {
            $permissionCheck = $gateRule;
        }

        if (enum_exists($acl) && defined($acl . "::{$permissionCheck}")) {
            Acl::acl(constant($acl . '::' . $permissionCheck)->value, true);
        } elseif (enum_exists($acl) && method_exists($acl, $permissionCheck)) {
            if (!$acl::$permissionCheck($request)) {
                abort(403, 'Acesso negado');
            }
        } else {
            $message = config('app.debug')
                ? "Permissão não encontrada: {$acl}::{$permissionCheck}"
                : 'Acesso negado';
            abort(403, $message);
        }

        return $next($request);
    }
}
