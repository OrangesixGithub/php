<?php

namespace Orangesix\Service\Core;

use Illuminate\Database\Eloquent\Model;
use Orangesix\Core\AutoClassResolver;
use Orangesix\Repository\Contract\Repository;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        return $this->getRepository()->getModel();
    }

    /**
     * Retorna o repository vinculado ao service.
     *
     * @return Repository
     */
    protected function getRepository(): Repository
    {
        if ($this->repository === null) {
            abort(400, 'Repository não definido no service. A instância automática do repository foi desativada ou nenhum repository foi informado.');
        }
        return $this->repository;
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

    /**
     * Executa o hook registrado manualmente e a rule encontrada.
     *
     * @param string $name Nome interno do hook.
     * @param mixed ...$arguments Argumentos originais do hook.
     * @return void
     */
    private function runHook(string $name, mixed ...$arguments): void
    {
        $arguments = array_values($arguments);

        $hook = $this->getHook($name);
        if ($hook instanceof \Closure) {
            $hook(...$arguments);
        }

        $rule = AutoClassResolver::findServiceRule(AutoClassResolver::normalize($this->getModel()::class, 'Model'), $name);
        if (empty($rule)) {
            return;
        }

        $instance = app()->make($rule);
        if (!is_callable($instance)) {
            abort(500, "A rule {$rule} precisa implementar o método __invoke.");
        }
        $instance(...array_merge($arguments, [$this]));
    }

    /**
     * Trata exceções comuns dos fluxos de CRUD.
     *
     * @param \Exception $exception
     * @return never
     * @throws \Exception|HttpExceptionInterface
     */
    private function abortException(\Exception $exception): never
    {
        if ($exception instanceof HttpExceptionInterface) {
            throw $exception;
        }
        if ($exception->getCode() == '23000') {
            abort(400, "Este registro está sendo utilizado em outro módulo do sistema.
                <p class='mt-2'><a class='j_message_detail d-flex w-100 fs-7 text-white fw-semibold' href='#'><i class='bi bi-eye me-1'></i>Veja detalhe:</a></p>
                <p id='j_message_detail_view' class='fs-7 mt-2' style='display: none'>({$exception->getMessage()})</p>
           ");
        }
        abort(500, $exception->getMessage());
    }
}
