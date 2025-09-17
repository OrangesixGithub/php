<?php

return [

    /*
    |----------------------------------------------------------------------
    | Acl Filial
    |----------------------------------------------------------------------
    |
    | This option defines whether ACL package will work with branch system
    |
    */

    'filial' => env('ACL_FILIAL', false),

    /*
    |----------------------------------------------------------
    | Acl Models
    |----------------------------------------------------------
    |
    | This option defines the model corresponds to the strategy
    |
    */

    'models' => [
        'user' => env('ACL_USER_MODEL', 'App\Models\User'),
        'user_filial' => env('ACL_USER_FILIAL_MODEL', 'App\Models\UserFilial'),
    ],

    /*
    |----------------------------------------------------------
    | Acl Gates
    |----------------------------------------------------------
    |
    | This option defines the verification rule in the
    | ACL package middleware
    |
    */

    'gate_default' => env('ACL_GATE_DEFAULT', 'VISUALIZAR'),

    'gates' => [
        'index' => 'VISUALIZAR',
        'create' => 'CADASTRAR',
        'edit' => 'EDITAR',
        'manager' => 'GERENCIAR',
        'delete' => 'EXCLUIR',
    ],

    /*
    |----------------------------------------------------------------------
    | Acl Rules
    |----------------------------------------------------------------------
    |
    | This option defines all the module, group and permission (default) of the
    | application. If you wanted to edit and add more options according to your need
    |
    */

    'rules' => [
        'module' => [
            1 => 'Configuração'
        ],
        'group' => [
            ['id' => 1, 'id_permissoes_modulo' => 1, 'nome' => 'Permissões'],
            ['id' => 2, 'id_permissoes_modulo' => 1, 'nome' => 'Produto'],
        ],
        'permissions' => [
            ['id' => 1, 'id_permissoes_grupo' => 1, 'nome' => 'Visualizar'],
            ['id' => 2, 'id_permissoes_grupo' => 1, 'nome' => 'Gerenciar'],
            ['id' => 3, 'id_permissoes_grupo' => 2, 'nome' => 'Visualizar'],
            ['id' => 4, 'id_permissoes_grupo' => 2, 'nome' => 'Gerenciar'],
        ]
    ],

    /*
    |----------------------------------------------------------------
    | Acl Session
    |----------------------------------------------------------------
    |
    | This option defines the default name that will be assigned the
    | application management of the application
    |
    */

    'session' => env('ACL_SESSION', 'app')
];
