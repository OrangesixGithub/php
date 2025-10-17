<?php

namespace Orangesix\Models\Core;

use Illuminate\Contracts\Container\BindingResolutionException;

trait ModelAutoInstance
{
    /**
     * @param string $class
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function instanceAutoModel(string $class): mixed
    {
        $model = str_replace('model', '', $class) . 'Model';
        $paths = empty(config('model_path')) ? [
            app_path('Model'),
            app_path('Models'),
        ] : config('model_path');
        foreach ($paths as $modelPath) {
            $instance = getClass($modelPath, $model);
            if (!empty($instance)) {
                break;
            }
        }

        if (!empty($instance)) {
            $class = $instance['namespace'] . '\\' . $instance['class'];
            return app()->make($class);
        }
        return null;
    }

    /**
     * Procura a classe compativel com nome do repository instanciado
     * @return mixed
     * @throws BindingResolutionException
     */
    private function getClassModelAuto(): mixed
    {
        $reflection = new \ReflectionClass($this);
        $modelAuto = str_replace('Repository', '', class_basename($reflection->getName()));

        return $this->instanceAutoModel($modelAuto);
    }
}
