<?php

namespace Orangesix\Acl\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Orangesix\Acl\Enum\AclPermissionsAction;

/**
 * Facade - Acl
 * @method static array getModule()
 * @method static array getGroup(?int $id_module = null)
 * @method static array getPermissions(?int $id_group = null)
 * @method static array getProfile(?int $id_filial = null)
 * @method static array getProfilePermissions(int $id_profile)
 * @method static array getUserPermissions(int $id_user)
 * @method static void setAcl()
 * @method static void setProfile(Request $request)
 * @method static void managerPermissions(int $binding, array|int $id_permissions, AclPermissionsAction $action = AclPermissionsAction::Profile, bool $active = true)
 * @method static void managerProfile(int $binding, array|int $id_profile, bool $active = true)
 * @method static void delete(int $binding, AclPermissionsAction $action = AclPermissionsAction::Profile)
 * @method static array|bool acl(int|array $permissions = [], bool $exception = false)
 * @method static void aclLoad(?int $id_filial = null)
 *
 * @see \Orangesix\Acl\Acl
 */
class Acl extends Facade
{
    /**
     * Get the application instance behind the facade.
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return 'acl';
    }
}
