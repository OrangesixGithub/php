<?php

return [

    'filial' => env('ACL_FILIAL', false),

    'models' => [
        'user' => env('ACL_USER_MODEL', 'App\Models\User'),
    ],

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
    ]
];
