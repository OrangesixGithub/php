<?php

namespace Orangesix\Service\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orangesix\Service\ServiceBase;

trait ServiceBaseDelete
{
    /**
     * Realiza a exclusão do registro através do repository vinculado ao service.
     *
     * @param array|Request $request
     * @return void
     * @throws \Throwable
     */
    public function delete(array|Request $request): void
    {
        try {
            DB::beginTransaction();

            $id = is_array($request) ? $request['id'] : $request->id;

            $beforeDelete = $this->getHook('beforeDelete');
            if ($beforeDelete instanceof \Closure) {
                $beforeDelete($request);
            }

            $this->repository->remove($id);

            $afterDelete = $this->getHook('afterDelete');
            if ($afterDelete instanceof \Closure) {
                $afterDelete($request);
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
     * Define uma closure para ser executada antes do delete.
     *
     * @param \Closure $closure
     * @return ServiceBase|ServiceBaseDelete
     */
    public function beforeDelete(\Closure $closure): self
    {
        $this->setHook('beforeDelete', $closure);
        return $this;
    }

    /**
     * Define uma closure para ser executada depois do delete.
     *
     * @param \Closure $closure
     * @return ServiceBase|ServiceBaseDelete
     */
    public function afterDelete(\Closure $closure): self
    {
        $this->setHook('afterDelete', $closure);
        return $this;
    }
}
