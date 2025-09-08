<?php

namespace Orangesix\Acl;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Orangesix\Acl\Exceptions\Acl;

trait HasAcl
{
    /**
     * Realiza a validação das permissões na sessão de acordo com parâmetro
     * @param int|array $permission
     * @return array | bool
     */
    public static function acl(int|array $permissions = [], bool $exception = false): array|bool
    {
        $acl = session()->get('acl_' . config('acl.session')) ?? [];
        if (is_int($permissions)) {
            if (!$exception) {
                return in_array($permissions, $acl);
            }
            $validated = in_array($permissions, $acl);
        } else {
            $result = [];
            foreach ($permissions as $item) {
                $result[$item] = in_array($item, $acl);
            }
            $validated = collect($acl)->filter(function ($item) use ($permissions) {
                return in_array($item, $permissions);
            })->count() > 0;
        }
        if (!$exception) {
            return $result;
        }
        if ($exception && !$validated) {
            throw new Acl('Você não possui permissão para acessar este recurso!', 403);
        }
        return $validated;
    }

    /**
     * Realiza o carregamento das permissões após LOGIN do usuário
     * @param int|null $id_filial
     * @return void
     */
    public static function aclLoad(?int $id_filial = null): void
    {
        $user = Auth::id();
        if (empty($user)) {
            return;
        }
        $profile = DB::table('acl_perfil')
            ->select('acl_perfil.id')
            ->when(config('acl.filial'), function ($query) use ($user, $id_filial) {
                $query->join('usuario_filial_acl_perfil', 'usuario_filial_acl_perfil.id_acl_perfil', '=', 'acl_perfil.id');
                $query->join('usuario_filial', 'usuario_filial.id', '=', 'usuario_filial_acl_perfil.id_usuario_filial');
                if (!empty($id_filial)) {
                    $query->where('acl_perfil.id_filial', $id_filial);
                }
                $query->where('usuario_filial.id_usuario', $user);
            })
            ->when(!config('acl.filial'), function ($query) use ($user) {
                $query->join('usuario_acl_perfil', 'usuario_acl_perfil.id_acl_perfil', '=', 'acl_perfil.id');
                $query->where('usuario_acl_perfil.id_usuario', $user);
            })
            ->get()
            ->groupBy('id')
            ->keys()
            ->all();

        $permissionsProfile = DB::table('acl_perfil_permissoes')
            ->select('acl_permissoes.id')
            ->join('acl_permissoes', 'acl_permissoes.id', '=', 'acl_perfil_permissoes.id_permissoes')
            ->whereIn('acl_perfil_permissoes.id_perfil', $profile)
            ->where('acl_permissoes.ativo', '=', 'S');

        $permissionsUsers = DB::table('acl_permissoes_usuario')
            ->select('acl_permissoes.id')
            ->when(config('acl.filial'), function ($query) use ($user, $id_filial) {
                $query->join('usuario_filial', 'usuario_filial.id', '=', 'acl_permissoes_usuario.id_usuario_filial');
                $query->where('usuario_filial.id_filial', $id_filial);
                $query->where('usuario_filial.id_usuario', $user);
            })
            ->when(!config('acl.filial'), function ($query) use ($user) {
                $query->where('acl_permissoes_usuario.id_usuario', $user);
            })
            ->join('acl_permissoes', 'acl_permissoes.id', '=', 'acl_permissoes_usuario.id_permissoes')
            ->union($permissionsProfile)
            ->get();
        session(['acl_' . config('acl.session') => $permissionsUsers->groupBy('id')->keys()->all()]);
    }
}
