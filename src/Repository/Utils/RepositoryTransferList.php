<?php

namespace Orangesix\Repository\Utils;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orangesix\Repository\RepositoryBase;

trait RepositoryTransferList
{
    /** @var Model */
    private Model $transferList;

    /** @var array */
    private array $keys;

    /**
     * Realiza a parametrização do transferList
     * @throws BindingResolutionException
     */
    public function transferList(string $model, array $keys = []): RepositoryBase
    {
        $this->transferList = app()->make($model);
        $this->keys = $keys;
        return $this;
    }

    /**
     * Realiza o gerenciamento do transferList
     */
    public function manager(Request $request): void
    {
        try {
            DB::beginTransaction();
            if (empty($request->list) || !isset($this->keys[0]) || !isset($this->keys[1])) {
                abort(500, 'Não foi possível atualizar a lista de dados.');
            }
            foreach ($request->list as $list) {
                if (isset($list['active']) && $list['active']) {
                    $this->active([
                        $this->keys[0] => $request[$this->keys[0]],
                        $this->keys[1] => $list[$this->keys[1]],
                    ]);
                } else {
                    $this->disabled([
                        $this->keys[0] => $request[$this->keys[0]],
                        $this->keys[1] => $list[$this->keys[1]],
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(500, $exception->getMessage());
        }
    }

    /**
     * Realiza a ativação do transfer list
     */
    private function active(array $data): void
    {
        $this->transferList::updateOrCreate(
            [
                $this->keys[0] => $data[$this->keys[0]],
                $this->keys[1] => $data[$this->keys[1]]
            ],
            $data
        )?->id;
    }

    /**
     * Realiza a desativação do transfer list
     */
    private function disabled(array $data): void
    {
        $this->transferList::where($this->keys[0], $data[$this->keys[0]])
            ->where($this->keys[1], '=', $data[$this->keys[1]])
            ->delete();
    }
}
