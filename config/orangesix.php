<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Service Paths
    |--------------------------------------------------------------------------
    |
    | Directories where the package should look for application services when
    | resolving classes by the company naming convention, such as ProdutoService.
    |
    */

    'service_path' => [
        app_path('Services'),
        app_path('Service'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Repository Paths
    |--------------------------------------------------------------------------
    |
    | Directories where the package should look for application repositories,
    | such as ProdutoRepository.
    |
    */

    'repository_path' => [
        app_path('Repositories'),
        app_path('Repository'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Paths
    |--------------------------------------------------------------------------
    |
    | Directories where the package should look for application models, such as
    | ProdutoModel. For default CRUD, the model is the minimum required class.
    |
    */

    'model_path' => [
        app_path('Models'),
        app_path('Model'),
    ],
];
