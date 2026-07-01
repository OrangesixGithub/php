<?php

namespace Orangesix\Repository\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Orangesix\Core\AutoClassResolver;

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
        return AutoClassResolver::makeRepository(AutoClassResolver::normalize($class, 'Repository'));
    }

    /**
     * Procura a classe compativel com nome do service instanciado
     * @return mixed
     * @throws BindingResolutionException
     */
    private function getClassRepositoryAuto(): mixed
    {
        $reflection = new \ReflectionClass($this);
        $repositoryAuto = AutoClassResolver::normalize($reflection->getName(), 'Service');

        return $this->instanceAutoRepository($repositoryAuto);
    }
}
