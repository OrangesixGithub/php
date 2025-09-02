<?php

namespace Orangesix\Acl\Repository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Orangesix\Acl\Model\PermissoesModel;
use Orangesix\Acl\Model\PermissoesUsuarioModel;
use Orangesix\Repository\Contract\Repository;
use Orangesix\Repository\Core\RepositoryDataBase;

/**
 * Repository - Permissões Usuário Acl
 */
class PermissoesUsuarioAclRepository implements Repository
{
    use RepositoryDataBase;

    private PermissoesUsuarioModel $model;

    private PermissoesModel $permissoes;

    public function __construct(
        PermissoesUsuarioModel $model,
        PermissoesModel        $permissoes
    ) {
        $this->model = $model;
        $this->permissoes = $permissoes;
    }

    /**
     * Realiza a pesquisa das permissões por usuário - Permissões Usuário Acl
     */
    public function findAll(?int $grupo, ?int $user): Collection
    {
        $permissoesUser = $this->model::select('*')
            ->where('id_usuario_filial', '=', $user)
            ->get();
        $permissoes = DB::table('usuario_filial_acl_perfil')
            ->select(['acl_perfil_permissoes.id_permissoes'])
            ->join('acl_perfil', 'acl_perfil.id', '=', 'usuario_filial_acl_perfil.id_acl_perfil')
            ->join('acl_perfil_permissoes', 'acl_perfil_permissoes.id_perfil', '=', 'acl_perfil.id')
            ->where('usuario_filial_acl_perfil.id_usuario_filial', '=', $user)
            ->get()
            ->groupBy('id_permissoes')
            ->keys()
            ->all();
        return $this->permissoes::where('id_permissoes_grupo', $grupo)
            ->whereNotIn('id', $permissoes)
            ->where('ativo', 'S')
            ->orderBy('id')
            ->get()
            ->map(function ($item) use ($permissoesUser) {
                $data = $item;
                $data->active = !is_bool($permissoesUser->search(function ($value) use ($item) {
                    return $item->id == $value->id_permissoes;
                }));
                $data->label = $item->id . ' -> ' . $item->nome;
                return $data;
            });
    }
}
