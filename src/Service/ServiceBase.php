<?php

namespace Orangesix\Service;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orangesix\Repository\Core\RepositoryAutoInstance;
use Orangesix\Repository\Contract\Repository;
use Orangesix\Repository\RepositoryBase;
use Orangesix\Service\Contract\Service;
use Orangesix\Service\Core\ServiceAutoInstance;
use Orangesix\Service\Core\ServiceDataBaseEvent;
use Orangesix\Service\Response\ServiceResponse;

/**
 * @property RepositoryBase $repository
 */
abstract class ServiceBase implements Service
{
    use ServiceAutoInstance;
    use ServiceDataBaseEvent;
    use RepositoryAutoInstance;

    /** @var ServiceResponse */
    protected ServiceResponse $response;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(
        protected ?Repository $repository = null,
        private array         $validated = []
    ) {
        $this->response = app()->make(ServiceResponse::class);
        $this->repository = empty($this->repository) ? $this->getClassRepositoryAuto() : $this->repository;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws BindingResolutionException
     */
    public function __get(string $name)
    {
        return $this->instanceAutoService($name);
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
            $parameters = array_pad($arguments, $reflection->getNumberOfParameters(), null);

            return $this->repository->$name(...$parameters);
        } else {
            throw new \BadMethodCallException('Método não existe no service ou repository.', 500);
        }
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->repository->getModel();
    }

    /**
     * @param mixed $paramns
     * @return mixed
     */
    public function find(mixed ...$paramns): mixed
    {
        return $this->repository->find(...$paramns);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function manager(Request $request): mixed
    {
        if (method_exists($request, 'validated')) {
            $data = $request->validated();
        } else {
            if (!empty($this->validated)) {
                $data = $request->validate($this->validated);
            } else {
                $data = $request->all();
            }
        }
        try {
            DB::beginTransaction();

            if ($this->beforeManager instanceof \Closure) {
                ($this->beforeManager)($data);
            }

            $id = $this->repository->save($data);

            if ($this->afterManager instanceof \Closure) {
                ($this->afterManager)(array_merge($data, ['id' => $id]));
            }

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
     * @param Request $request
     * @return void
     */
    public function delete(Request $request): void
    {
        try {
            DB::beginTransaction();

            if ($this->beforeDelete instanceof \Closure) {
                ($this->beforeDelete)($request->all());
            }

            $this->repository->remove($request->id);

            if ($this->afterDelete instanceof \Closure) {
                ($this->afterDelete)($request->all());
            }

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
     * @param array $validated
     * @return $this
     */
    public function setValidated(array $validated): self
    {
        $this->validated = $validated;
        return $this;
    }
}
