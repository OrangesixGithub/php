<?php

if (!function_exists('IsActiveRoute')) {
    /**
     * @param string|array $route
     * @param mixed $return
     * @param mixed $falied
     * @return mixed
     */
    function IsActiveRoute(
        string|array $route,
        mixed        $return = 'active',
        mixed        $falied = ''
    ): mixed {
        if (!class_exists("\Illuminate\Support\Facades\Request")) {
            return $falied;
        }
        $url = \Illuminate\Support\Facades\Request::url();
        if (is_array($route)) {
            return in_array($url, $route) ? $return : $falied;
        }
        return $url === $route ? $return : $falied;
    }
}
