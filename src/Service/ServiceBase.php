<?php

namespace Orangesix\Service;

use Illuminate\Contracts\Container\BindingResolutionException;
use Orangesix\Core\AutoInstanceConfig;
use Orangesix\Repository\Contract\Repository;
use Orangesix\Repository\Core\RepositoryAutoInstance;
use Orangesix\Repository\RepositoryBase;
use Orangesix\Service\Contract\Service;
use Orangesix\Service\Core\ServiceAutoInstance;
use Orangesix\Service\Core\ServiceBaseCore;
use Orangesix\Service\Core\ServiceBaseDelete;
use Orangesix\Service\Core\ServiceBaseManager;
use Orangesix\Service\Response\ServiceResponse;

/**
 * Service Base
 * @property RepositoryBase $repository
 */
abstract class ServiceBase implements Service
{
    use ServiceBaseCore;
    use ServiceBaseDelete;
    use ServiceBaseManager;
    use ServiceAutoInstance;
    use RepositoryAutoInstance;

    /** @var ServiceResponse */
    protected ServiceResponse $response;

    /**
     * Inicializa o service e garante que ele sempre tenha um repository.
     *
     * O container injeta o `ServiceResponse` (helper de respostas padronizadas).
     * Se nenhum repository for informado, o pacote resolve automaticamente o
     * repository correspondente pela convenção de nomes ({Nome}Service =>
     * {Nome}Repository) através de `getClassRepositoryAuto()`.
     *
     * @param Repository|null $repository Repository explícito; quando nulo, é resolvido por convenção.
     * @throws BindingResolutionException Quando o container não consegue resolver o ServiceResponse ou o repository.
     */
    public function __construct(
        protected ?Repository $repository = null
    ) {
        $this->response = app()->make(ServiceResponse::class);
        $this->repository = empty($this->repository) && !AutoInstanceConfig::repository($this)
            ? $this->getClassRepositoryAuto()
            : $this->repository;
    }

    /**
     * Resolve dependências acessadas como propriedade mágica (ex.: $this->produtoService).
     *
     * Se o nome da propriedade começar com "repository", retorna a instância do
     * repository correspondente; caso contrário, resolve um service pela
     * convenção de nomes. Permite acessar outros services/repositories sob
     * demanda, sem injeção manual no construtor.
     *
     * @param string $name Nome da propriedade acessada (ex.: "clienteRepository", "pedidoService").
     * @return mixed Instância do service ou repository resolvido.
     * @throws BindingResolutionException Quando o container não consegue instanciar a classe resolvida.
     */
    public function __get(string $name)
    {
        if (str_starts_with(strtolower($name), 'repository')) {
            return $this->instanceAutoRepository($name);
        }
        return $this->instanceAutoService($name);
    }

    /**
     * Delega ao repository métodos não declarados no service (proxy de instância).
     *
     * Quando um metodo chamado não existe no service, mas existe no repository
     * vinculado, a chamada é encaminhada para ele. Os argumentos são preenchidos
     * com `null` até atingir o número de parâmetros do metodo de destino, evitando
     * erro por argumento ausente. Se o metodo também não existir no repository,
     * lança exceção.
     *
     * @param string $name Nome do método chamado.
     * @param array $arguments Argumentos passados na chamada.
     * @return mixed Retorno do método correspondente no repository.
     * @throws \BadMethodCallException Quando o método não existe nem no service nem no repository.
     */
    public function __call(string $name, array $arguments): mixed
    {
        if ($this->repository !== null && method_exists($this->repository, $name)) {
            $repository = $this->getRepository();
            $reflection = new \ReflectionMethod($repository, $name);
            $parameters = array_pad($arguments, $reflection->getNumberOfParameters(), null);

            return $repository->$name(...$parameters);
        }
        $model = $this->getModel();
        if (method_exists($model, $name)) {
            return forward_static_call_array([get_class($model), $name], $arguments);
        }
        throw new \BadMethodCallException('Método não existe no service, repository ou model.', 500);
    }

    /**
     * Delega chamadas estáticas para o repository ou para o model (proxy estático).
     *
     * Instancia o service via container e tenta, nesta ordem:
     *  1. encaminhar para um metodo estático de mesmo nome no repository;
     *  2. encaminhar para um metodo (estático/scope) de mesmo nome no model.
     * Útil para expor, de forma estática, scopes e helpers do model/repository
     * sem precisar instanciar o service manualmente.
     *
     * @param string $name Nome do metodo estático chamado.
     * @param array $arguments Argumentos passados na chamada.
     * @return mixed Retorno do metodo correspondente no repository ou no model.
     * @throws BindingResolutionException Quando o container não consegue instanciar o service.
     * @throws \BadMethodCallException Quando o metodo não existe no repository nem no model.
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (static::class === DefaultService::class) {
            throw new \LogicException(
                'DefaultService não pode receber chamadas estáticas. Use a chamada por instância, como $this->service->'
                . $name . '(), ou crie um service específico.'
            );
        }
        $service = app()->make(static::class);
        $repository = $service->getRepository();
        if (method_exists($repository, $name)) {
            $reflection = new \ReflectionMethod($repository, $name);
            if ($reflection->isStatic()) {
                return forward_static_call_array([get_class($repository), $name], $arguments);
            }
        }
        $model = $service->getModel();
        if (method_exists($model, $name)) {
            return forward_static_call_array([$model, $name], $arguments);
        }
        throw new \BadMethodCallException('Método não existe no service ou repository.', 500);
    }
}
