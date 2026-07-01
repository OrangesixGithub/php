<?php

namespace Orangesix\Service\Core;

use Illuminate\Database\Eloquent\Model;

trait ServiceBaseCore
{
    /** @var \WeakMap<object, array<string, \Closure>>|null */
    private static ?\WeakMap $crudHooks = null;

    /**
     * Retorna o model vinculado ao service.
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->repository->getModel();
    }

    /**
     * Registra um gatilho de CRUD somente quando ele for utilizado.
     *
     * @param string $name Nome interno do gatilho.
     * @param \Closure $closure Callback que será executado no fluxo do CRUD.
     * @return void
     */
    private function setHook(string $name, \Closure $closure): void
    {
        self::$crudHooks ??= new \WeakMap();
        $hooks = self::$crudHooks[$this] ?? [];
        $hooks[$name] = $closure;
        self::$crudHooks[$this] = $hooks;
    }

    /**
     * Recupera um gatilho de CRUD registrado para a instância atual.
     *
     * @param string $name Nome interno do gatilho.
     * @return \Closure|null
     */
    private function getHook(string $name): ?\Closure
    {
        if (self::$crudHooks === null) {
            return null;
        }

        return self::$crudHooks[$this][$name] ?? null;
    }
}
