<?php

namespace Orangesix\Models\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Orangesix\Core\AutoClassResolver;

trait ModelAutoInstance
{
    /**
     * @param string $class
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function instanceAutoModel(string $class): mixed
    {
        return AutoClassResolver::makeModel(AutoClassResolver::normalize($class, 'Model'));
    }

    /**
     * Procura a classe compativel com nome do repository instanciado
     * @return mixed
     * @throws BindingResolutionException
     */
    private function getClassModelAuto(): mixed
    {
        $reflection = new \ReflectionClass($this);
        $modelAuto = AutoClassResolver::normalize($reflection->getName(), 'Repository');
        return $this->instanceAutoModel($modelAuto);
    }
}
