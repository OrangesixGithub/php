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
        if (is_array($request)) {
            $data = $request;
        } elseif (method_exists($request, 'validated')) {
            $data = $request->validated();
        } else {
            $data = $request->all();
        }

        try {
            DB::beginTransaction();

            $beforeManager = $this->getHook('beforeManager');
            if ($beforeManager instanceof \Closure) {
                $beforeManager($data);
            }

            $id = $this->repository->save($data);

            $afterManager = $this->getHook('afterManager');
            if ($afterManager instanceof \Closure) {
                $afterManager(array_merge($data, ['id' => $id]));
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
}
