<?php

namespace Orangesix\Acl\Repository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Orangesix\Acl\Model\PermissoesGrupoModel;
use Orangesix\Acl\Model\PermissoesModel;
use Orangesix\Acl\Model\PermissoesModuloModel;
use Orangesix\Repository\Contract\Repository;
use Orangesix\Repository\Core\RepositoryDataBase;

/**
 * Repository - Permissões Acl
 */
class PermissoesAclRepository implements Repository
{
    use RepositoryDataBase;

    private PermissoesModel $model;

    private PermissoesGrupoModel $permissoesGrupo;

    private PermissoesModuloModel $permissoesModulo;

    public function __construct(
        PermissoesModel       $permissoes,
        PermissoesGrupoModel  $permissoesGrupo,
        PermissoesModuloModel $permissoesModulo
    ) {
        $this->model = $permissoes;
        $this->permissoesModulo = $permissoesModulo;
        $this->permissoesGrupo = $permissoesGrupo;
    }

    /**
     * Realiza a pesquisa - Permissões Acl
     */
    public function findAll(int $grupo = null): Collection
    {
        return $this->model::select('*')
            ->where('ativo', 'S')
            ->when(!empty($grupo) ? $grupo : false, function ($query, $grupo) {
                $query->where('id_permissoes_grupo', $grupo);
            })
            ->get();
    }

    /**
     * Realiza a pesquisa dos módulos - Permissões Acl
     */
    public function findModulo(): Collection
    {
        return $this->permissoesModulo::all()
            ->map(function ($item) {
                $data = $item;
                $data->name = $item->nome;
                return $data;
            });
    }

    /**
     * Realiza a pesquisa do grupo de permissões - Permissões Acl
     */
    public function findGrupo(int $modulo = null): Collection
    {
        return $this->permissoesGrupo::select('*')
            ->when(!empty($modulo) ? $modulo : false, function ($query, $modulo) {
                $query->where('id_permissoes_modulo', $modulo);
            })
            ->get()
            ->map(function ($item) {
                $data = $item;
                $data->name = $item->nome;
                return $data;
            });
    }

    /**
     * Realiza a pesquisa permissões por usuário - Permissões Acl
     */
    public function findAllUser(?int $grupo = null, ?int $user = null): Collection
    {
        $permissoes = DB::table('usuario_filial_acl_perfil')
            ->select(['acl_perfil_permissoes.id_permissoes'])
            ->join('acl_perfil', 'acl_perfil.id', '=', 'usuario_filial_acl_perfil.id_acl_perfil')
            ->join('acl_perfil_permissoes', 'acl_perfil_permissoes.id_perfil', '=', 'acl_perfil.id')
            ->where('usuario_filial_acl_perfil.id_usuario_filial', '=', $user)
            ->get()
            ->groupBy('id_permissoes')
            ->keys()
            ->all();
        return $this->model::select('*')
            ->where('ativo', 'S')
            ->whereNotIn('id', $permissoes)
            ->when(!empty($grupo) ? $grupo : false, function ($query, $grupo) {
                $query->where('id_permissoes_grupo', $grupo);
            })
            ->get();
    }
}
