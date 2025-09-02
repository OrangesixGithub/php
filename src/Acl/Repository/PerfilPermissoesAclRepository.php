<?php

namespace Orangesix\Acl\Repository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Orangesix\Acl\Model\PerfilPermissoesModel;
use Orangesix\Acl\Model\PermissoesModel;
use Orangesix\Repository\Contract\Repository;
use Orangesix\Repository\Core\RepositoryDataBase;

/**
 * Repository - Perfil Permissões Acl
 */
class PerfilPermissoesAclRepository implements Repository
{
    use RepositoryDataBase;

    private PerfilPermissoesModel $model;

    private PermissoesModel $modelPermissoes;

    public function __construct(
        PerfilPermissoesModel $perfil,
        PermissoesModel       $modelPermissoes
    ) {
        $this->model = $perfil;
        $this->modelPermissoes = $modelPermissoes;
    }

    /**
     * Realiza a pesquisa das permissões por perfil - Perfil Pemissões Acl
     */
    public function findAll(int $perfil, int $grupo): Collection
    {
        $perfilPermissoes = DB::table('acl_perfil_permissoes')
            ->where('id_perfil', $perfil)
            ->get();
        return $this->modelPermissoes::where('id_permissoes_grupo', $grupo)
            ->where('ativo', 'S')
            ->orderBy('id')
            ->get()
            ->map(function ($item) use ($perfilPermissoes) {
                $data = $item;
                $data->active = !is_bool($perfilPermissoes->search(function ($value) use ($item) {
                    return $item->id == $value->id_permissoes;
                }));
                $data->label = $item->id . ' -> ' . $item->nome;
                return $data;
            });
    }
}
