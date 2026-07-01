<?php

namespace Orangesix\Service\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orangesix\Service\ServiceBase;

trait ServiceBaseManager
{
    /**
     * Realiza o insert ou update do registro através do repository vinculado ao service.
     *
     * @param array|Request $request
     * @return mixed
     * @throws \Throwable
     */
    public function manager(array|Request $request): mixed
    {
        $data = $this->resolveManagerData($request);

        try {
            DB::beginTransaction();

            $this->runHook(name: 'beforeManager', arguments: $data);

            $id = $this->repository->save($data);

            $this->runHook(name: 'afterManager', arguments: array_merge($data, ['id' => $id]));

            DB::commit();
            return $id;
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->abortException($exception);
        }
    }

    /**
     * Define uma closure para ser executada antes do manager.
     *
     * @param \Closure $closure
     * @return ServiceBase|ServiceBaseManager
     */
    public function beforeManager(\Closure $closure): self
    {
        $this->setHook('beforeManager', $closure);
        return $this;
    }

    /**
     * Define uma closure para ser executada depois do manager.
     *
     * @param \Closure $closure
     * @return ServiceBase|ServiceBaseManager
     */
    public function afterManager(\Closure $closure): self
    {
        $this->setHook('afterManager', $closure);
        return $this;
    }

    /**
     * Resolve os dados usados pelo manager a partir de array, FormRequest ou Request comum.
     *
     * @param array|Request $request
     * @return array
     */
    private function resolveManagerData(array|Request $request): array
    {
        if (is_array($request)) {
            return $request;
        }
        if (method_exists($request, 'validated')) {
            return $request->validated();
        }
        return $request->all();
    }
}
