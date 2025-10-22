<?php

namespace Orangesix\Repository\Core;

use Illuminate\Contracts\Container\BindingResolutionException;

trait RepositoryAutoInstance
{
    /**
     * Realiza a construção do objeto repository
     * @param string $class
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function instanceAutoRepository(string $class): mixed
    {
        $repository = str_replace('repository', '', $class) . 'Repository';
        $paths = empty(config('orangesix.repository_path')) ? [
            app_path('Repository'),
            app_path('Repositories'),
        ] : config('orangesix.repository_path');
        foreach ($paths as $repositoryPath) {
            $instance = getClass($repositoryPath, $repository);
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
     * Procura a classe compativel com nome do service instanciado
     * @return mixed
     * @throws BindingResolutionException
     */
    private function getClassRepositoryAuto(): mixed
    {
        $reflection = new \ReflectionClass($this);
        $repositoryAuto = str_replace('Service', '', class_basename($reflection->getName()));

        return $this->instanceAutoRepository($repositoryAuto);
    }
}
