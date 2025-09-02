<?php

namespace Orangesix\Acl\Repository;

use Illuminate\Database\Eloquent\Collection;
use Orangesix\Acl\Model\PerfilModel;
use Orangesix\Repository\Contract\Repository;
use Orangesix\Repository\Core\RepositoryDataBase;

/**
 * Repository - Perfil Acl
 */
class PerfilAclRepository implements Repository
{
    use RepositoryDataBase;

    private PerfilModel $model;

    public function __construct(PerfilModel $perfil)
    {
        $this->model = $perfil;
    }

    /**
     * Realiza a pesquisa - Perfil Acl
     */
    public function findAll(int $filial): Collection
    {
        return $this->model::where('id_filial', $filial)
            ->get()
            ->map(function ($item) {
                $data = $item;
                $data->id_acl_perfil = $item->id;
                $data->label = $item->nome;
                return $data;
            });
    }
}
