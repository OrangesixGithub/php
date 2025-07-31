<?php

namespace Orangesix\Service;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orangesix\Repository\Repository;
use Orangesix\Repository\RepositoryAutoInstance;
use Orangesix\Service\Response\ServiceResponse;

abstract class ServiceBase implements Service, ServiceDBEvent
{
    use ServiceDataBase;
    use ServiceAutoInstance;
    use RepositoryAutoInstance;

    /** @var Repository | null */
    protected ?Repository $repository;

    /** @var ServiceResponse */
    protected ServiceResponse $response;

    /** @var array */
    private ?array $autoInstance;

    /**
     * @var array
     */
    private array $validation = [];

    /**
     * @var array
     */
    private array $validationData = [];

    /**
     * @param Repository $repository
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(?Repository $repository = null, ?array $autoInstance = null)
    {
        $this->repository = $repository;
        $this->response = app()->make(ServiceResponse::class);
        $this->autoInstance = $autoInstance;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->repository, $name)) {
            $reflection = new \ReflectionMethod($this->repository, $name);
            $args = $reflection->getNumberOfParameters();
            for ($i = 0; $i < $args; $i++) {
                ${'arg_' . $i} = $arguments[$i] ?? null;
            }
            return $this->repository->$name($arg_0 ?? null, $arg_1 ?? null, $arg_2 ?? null, $arg_3 ?? null, $arg_4 ?? null, $arg_5 ?? null, $arg_6 ?? null, $arg_7 ?? null, $arg_8 ?? null, $arg_9 ?? null);
        } else {
            throw new \Exception('Método não existe no service ou repository.', 500);
        }
    }

    /**
     * @param string $name
     * @return mixed
     * @throws BindingResolutionException
     */
    public function __get(string $name)
    {
        if (strpos($name, 'service') !== false) {
            return $this->instanceAutoService($name, $this->autoInstance['service'] ?? null);
        }
        return $this->instanceAutoRepository($name, $this->autoInstance['repository'] ?? null);
    }

    /*
    |--------------------------------------------------------
    | interface - Service
    |--------------------------------------------------------
    | Implementação dos métodos da interface
    |
    */

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->repository->getModel();
    }

    /**
     * Realiza a pesquisa do modelo
     * @param int $id
     * @return mixed
     */
    public function find(int $id, ...$paramns): mixed
    {
        return $this->repository->find($id, ...$paramns);
    }

    /**
     * Realiza o cadastro ou atualização dos dados
     * @param Request $request
     * @return mixed
     */
    public function manager(Request $request): mixed
    {
        if (method_exists($request, 'getData')) {
            $data = $request->getData();
        } else {
            $data = $this->validated($request);
        }

        try {
            DB::beginTransaction();
            $this->beforeManager($request, $data);
            $id = $this->repository->save($data);
            $this->afterManager($request, $data);
            DB::commit();
            return $id;
        } catch (\Exception $exception) {
            DB::rollBack();
            if ($exception->getCode() == '23000') {
                abort(400, "Este registro está sendo utilizado em outro módulo do sistema.
                    <p class='mt-2'><a class='j_message_detail d-flex w-100 fs-7 text-white fw-semibold' href='#'><i class='bi bi-eye me-1'></i>Veja detalhe:</a></p>
                    <p id='j_message_detail_view' class='fs-7 mt-2' style='display: none'>({$exception->getMessage()})</p>
               ");
            }
            abort(500, $exception->getMessage());
        }
    }

    /**
     * Realiza a exclusão dos dados
     * @param Request $request
     * @return void
     */
    public function delete(Request $request): void
    {
        try {
            DB::beginTransaction();
            $this->beforeDelete($request);
            $this->repository->remove($request->id);
            $this->afterDelete($request);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            if ($exception->getCode() == '23000') {
                abort(400, "Este registro está sendo utilizado em outro módulo do sistema.
                    <p class='mt-2'><a class='j_message_detail d-flex w-100 fs-7 text-white fw-semibold' href='#'><i class='bi bi-eye me-1'></i>Veja detalhe:</a></p>
                    <p id='j_message_detail_view' class='fs-7 mt-2' style='display: none'>({$exception->getMessage()})</p>
               ");
            }
            abort(500, $exception->getMessage());
        }
    }

    /**
     * Realiza a validação dos dados - OVERRIDE
     * @param Request $request
     * @return array
     */
    public function validated(Request $request): array
    {
        $data = $request->validate($this->validation);

        return array_merge($data, $this->validationData);
    }

    /**
     * @param array $validation
     * @param array $data
     * @return $this
     */
    public function setValidated(array $validation, array $data = []): self
    {
        $this->validation = $validation;
        $this->validationData = $data;

        return $this;
    }

    /*
    |--------------------------------------------------------
    | interface - ServiceDBEvent
    |--------------------------------------------------------
    | Implementação dos métodos da interface
    |
    */

    /**
     * Executa método antes do manager ser executado - OVERRIDE
     * @param ...$paramns
     * @return void
     */
    public function beforeManager(...$paramns): void
    {
    }

    /**
     * Executa método depois do manager ser executado - OVERRIDE
     * @param ...$paramns
     * @return void
     */
    public function afterManager(...$paramns): void
    {
    }

    /**
     * Executa método antes do delete ser executado - OVERRIDE
     * @param ...$paramns
     * @return void
     */
    public function beforeDelete(...$paramns): void
    {
    }

    /**
     * Executa método depois do delete ser executado - OVERRIDE
     * @param ...$paramns
     * @return void
     */
    public function afterDelete(...$paramns): void
    {
    }
}
