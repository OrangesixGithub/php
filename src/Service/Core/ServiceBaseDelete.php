<?php

namespace Orangesix\Service\Core;

use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $id = $this->resolveDelete($request);
        try {
            DB::beginTransaction();

            $this->runHook(name: 'beforeDelete', arguments: $request);

            $this->repository->remove($id);

            $this->runHook(name: 'afterDelete', arguments: $request);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            if ($exception instanceof ModelNotFoundException) {
                abort(404, 'Registro não encontrado para exclusão.');
            }
            $this->abortException($exception);
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

    /**
     * Resolve o id usado na exclusão e falha com mensagem clara quando ele não existir.
     *
     * @param array|Request $request
     * @return int
     */
    private function resolveDelete(array|Request $request): int
    {
        $id = is_array($request) ? ($request['id'] ?? null) : $request->input('id');
        if (empty($id)) {
            abort(400, 'O campo id é obrigatório para exclusão.');
        }
        return (int)$id;
    }
}
