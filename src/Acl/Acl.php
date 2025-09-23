<?php

namespace Orangesix\Acl;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Orangesix\Acl\Enum\AclPermissionsAction;
use Orangesix\Acl\Model\PermissionsGroupModel;
use Orangesix\Acl\Model\PermissionsModel;
use Orangesix\Acl\Model\PermissionsModuleModel;
use Orangesix\Acl\Model\PermissionsUserModel;
use Orangesix\Acl\Model\ProfileModel;
use Orangesix\Acl\Model\ProfilePermissionsModel;
use Orangesix\Acl\Model\ProfileUserModel;

/**
 * Acl - Access Control Level
 * @package Orangesix\Acl
 */
class Acl
{
    use HasAcl;

    /**
     * @return array
     */
    public function getModule(): array
    {
        return PermissionsModuleModel::all()->toArray();
    }

    /**
     * @return array
     */
    public function getGroup(?int $id_module = null): array
    {
        return PermissionsGroupModel::query()
            ->when(!empty($id_module), function ($query) use ($id_module) {
                $query->where('id_permissoes_modulo', '=', $id_module);
            })
            ->get()
            ->toArray();
    }

    /**
     * @return array
     */
    public function getPermissions(?int $id_group = null): array
    {
        return PermissionsModel::query()
            ->when(!empty($id_group), function ($query) use ($id_group) {
                $query->where('id_permissoes_grupo', '=', $id_group);
            })
            ->get()
            ->toArray();
    }

    /**
     * @param int|null $id_filial
     * @return array
     * @throws \Exception
     */
    public function getProfile(?int $id_filial = null): array
    {
        if (config('acl.filial') && !$id_filial) {
            throw new \Exception('O parâmetro id_filial é obrigatório quando acl.filial_id está ativado');
        }
        return ProfileModel::query()
            ->when(config('acl.filial'), function ($query) use ($id_filial) {
                $query->where('id_filial', $id_filial);
            })
            ->get()
            ->toArray();
    }

    /**
     * @param int $id_profile
     * @return array
     */
    public function getProfilePermissions(int $id_profile): array
    {
        return ProfilePermissionsModel::query()
            ->select([
                'acl_permissoes.*',
                'acl_perfil_permissoes.id_perfil'
            ])
            ->join('acl_permissoes', 'acl_permissoes.id', '=', 'acl_perfil_permissoes.id_permissoes')
            ->where('id_perfil', $id_profile)
            ->get()
            ->toArray();
    }

    /**
     * @param int $id_user
     * @return array
     */
    public function getUserPermissions(int $id_user): array
    {
        $field = config('acl.filial') ? 'id_usuario_filial' : 'id_usuario';
        return PermissionsUserModel::query()
            ->select([
                'acl_permissoes.*',
                'acl_permissoes_usuario.' . $field
            ])
            ->join('acl_permissoes', 'acl_permissoes.id', '=', 'acl_permissoes_usuario.' . $field)
            ->where($field, $id_user)
            ->get()
            ->toArray();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function setAcl(): void
    {
        if (file_exists(database_path('seeders/AclSeeder.php'))) {
            Artisan::call('db:seed', ['--class' => 'AclSeeder']);
        } else {
            throw new \Exception('Não foi possível encontrar o arquivo AclSeeder.php na pasta database/seeders.');
        }
    }

    /**
     * @param Request $request
     * @return void
     */
    public function setProfile(Request $request): void
    {
        $data = $request->validate([
            'id' => '',
            'nome' => 'required',
            'id_filial' => config('acl.filial') ? 'required' : ''
        ]);
        ProfileModel::query()->updateOrCreate(['id' => $data['id'] ?? null], $data);
    }

    /**
     * @param int $binding
     * @param array|int $id_permissions
     * @param AclPermissionsAction $action
     * @param bool $active
     * @return void
     */
    public function managerPermissions(int $binding, array|int $id_permissions, AclPermissionsAction $action = AclPermissionsAction::Profile, bool $active = true): void
    {
        $bindingUserField = config('acl.filial') ? 'id_usuario_filial' : 'id_usuario';
        if ($active) {
            if (!is_array($id_permissions)) {
                $id_permissions = [$id_permissions];
            }
            foreach ($id_permissions as $id_permission) {
                if ($action->value == 'profile') {
                    if (!ProfileModel::query()->where('id', '=', $binding)->exists()) {
                        throw new \Exception('O perfil não existe na tabela acl_perfil.');
                    }
                    $data = ['id_perfil' => $binding, 'id_permissoes' => $id_permission];
                    ProfilePermissionsModel::query()->updateOrCreate($data, $data);
                } else {
                    $foregin = config('acl.filial')
                        ? app(config('acl.models.user_filial'))->getTable()
                        : app(config('acl.models.user'))->getTable();
                    if (!DB::table($foregin)->where('id', '=', $binding)->exists()) {
                        throw new \Exception('O usuário ' . (config('acl.filial') ? 'da filial' : '') . ' não existe na tabela ' . $foregin);
                    }
                    $data = [$bindingUserField => $binding, 'id_permissoes' => $id_permission];
                    PermissionsUserModel::query()->updateOrCreate($data, $data);
                }
            }
        } else {
            if (!is_array($id_permissions)) {
                $id_permissions = [$id_permissions];
            }
            if ($action->value == 'profile') {
                ProfilePermissionsModel::query()
                    ->where('id_perfil', $binding)
                    ->whereIn('id_permissoes', $id_permissions)
                    ->delete();
            } else {
                PermissionsUserModel::query()
                    ->where($bindingUserField, $binding)
                    ->whereIn('id_permissoes', $id_permissions)
                    ->delete();
            }
        }
    }

    /**
     * @param int $binding
     * @param array|int $id_profile
     * @param bool $active
     * @return void
     */
    public function managerProfile(int $binding, array|int $id_profile, bool $active = true): void
    {
        $bindingField = config('acl.filial') ? 'id_usuario_filial' : 'id_usuario';
        if (!is_array($id_profile)) {
            $id_profile = [$id_profile];
        }
        if ($active) {
            foreach ($id_profile as $id) {
                $foregin = config('acl.filial')
                    ? app(config('acl.models.user_filial'))->getTable()
                    : app(config('acl.models.user'))->getTable();
                if (!DB::table($foregin)->where('id', '=', $binding)->exists()) {
                    throw new \Exception('O usuário ' . (config('acl.filial') ? 'da filial' : '') . ' não existe na tabela ' . $foregin);
                }
                $data = ['id_acl_perfil' => $id, $bindingField => $binding];
                ProfileUserModel::query()->updateOrCreate($data, $data);
            }
        } else {
            ProfileUserModel::query()
                ->where($bindingField, $binding)
                ->whereIn('id_acl_perfil', $id_profile)
                ->delete();
        }
    }

    /**
     * @param int $binding
     * @param AclPermissionsAction $action
     * @return void
     */
    public function delete(int $binding, AclPermissionsAction $action = AclPermissionsAction::Profile): void
    {
        if ($action->value == 'profile') {
            ProfileModel::query()
                ->where('id', '=', $binding)
                ->delete();
        } else {
            $bindingField = config('acl.filial') ? 'id_usuario_filial' : 'id_usuario';
            PermissionsUserModel::query()
                ->where($bindingField, '=', $binding)
                ->delete();
        }
    }
}
