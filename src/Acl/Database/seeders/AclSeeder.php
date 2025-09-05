<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Orangesix\Acl\Model\PermissionsModel;
use Orangesix\Acl\Model\PermissionsGroupModel;
use Orangesix\Acl\Model\PermissionsModuleModel;

class AclSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!empty(config('acl.rules'))) {
            /**
             * Generate - Module
             */
            foreach (config('acl.rules.module') as $key => $value) {
                if (!PermissionsModuleModel::query()->where('id', '=', $key)->exists()) {
                    PermissionsModuleModel::create([
                        'id' => $key,
                        'nome' => $value
                    ]);
                }
            }

            /**
             * Generate Group
             */
            foreach (config('acl.rules.group') as $value) {
                if (!PermissionsGroupModel::query()->where('id', '=', $value['id'])->exists()) {
                    PermissionsGroupModel::create([
                        'id' => $value['id'],
                        'id_permissoes_modulo' => $value['id_permissoes_modulo'],
                        'nome' => $value['nome'],
                    ]);
                }
            }

            /**
             * Generate Permissions
             */
            foreach (config('acl.rules.permissions') as $value) {
                if (!PermissionsModel::query()->where('id', '=', $value['id'])->exists()) {
                    PermissionsModel::create([
                        'id' => $value['id'],
                        'id_permissoes_grupo' => $value['id_permissoes_grupo'],
                        'nome' => $value['nome'],
                    ]);
                }
            }
        }
    }
}
