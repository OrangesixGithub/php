<div align="center">
  <h1>@Orangesix/PHP</h1>
  <p>Uma biblioteca PHP integrada ao Laravel para padronizar CRUD, services, repositories, resources e ACL.</p>
</div>

<img src="https://img.shields.io/static/v1?label=License&message=MIT&color=success"/>
<img src="https://img.shields.io/static/v1?label=CORE&message=PHP&color=blue&logo=php"/>
<img src="https://img.shields.io/static/v1?label=Framework&message=Laravel&color=blue&logo=laravel"/>

## 📂 Estrutura do pacote

### 🛡️ Acl

Módulo responsável pelo gerenciamento de permissões, perfis de usuário, middleware, facade e diretivas Blade.

### 🎮 Controller

Classes base para controllers Laravel, com suporte à resolução automática de services.

### 🧮 Enum

Enums compartilhados pelo pacote.

### ⚠️ Exceptions

Classes de exceção personalizadas para respostas de API, mensagens e validação de campos.

### 🔧 Function

Helpers globais carregados pelo autoload do Composer.

### 🌐 HTTP

Resources para transformação e padronização de respostas JSON.

### 🗃️ Models

Traits e recursos centrais para integração com models Eloquent.

### 🏭 Repository

Camada de acesso a dados e consultas. O pacote usa a convenção `{Nome}Repository` e pode resolver repositories automaticamente.

### 💼 Service

Camada de regra de negócio. O pacote usa a convenção `{Nome}Service` e pode resolver services automaticamente.

## 📦 Instalação do pacote

Enquanto o pacote não possuir auto-discovery configurado no `composer.json`, registre o provider geral manualmente no projeto Laravel consumidor.

### 🚀 Laravel 11, 12 e 13

Adicione o provider geral em `bootstrap/providers.php`:

```php
<?php

use App\Providers\AppServiceProvider;
use Orangesix\OrangesixServiceProvider;

return [
    AppServiceProvider::class,
    OrangesixServiceProvider::class,
];
```

Depois limpe os caches da aplicação:

```bash
php artisan optimize:clear
```

### ⚙️ Configuração geral

Publique o arquivo de configuração geral do pacote:

```bash
php artisan vendor:publish --tag=orangesix-config
```

Isso cria:

- `config/orangesix.php`

Esse arquivo controla os caminhos usados pela resolução automática de services, repositories e models:

```php
'service_path' => [
    app_path('Service'),
    app_path('Services'),
],

'repository_path' => [
    app_path('Repository'),
    app_path('Repositories'),
],

'model_path' => [
    app_path('Model'),
    app_path('Models'),
],
```

## 🧱 Convenção de CRUD

O pacote segue a convenção de nomes adotada nos projetos da empresa:

```text
ProdutoModel
ProdutoRepository
ProdutoService
ProdutoController
```

Para CRUD padrão, a intenção é que o `Model` seja a classe mínima necessária. Quando `Service` ou `Repository` não existirem, o pacote pode usar sua infraestrutura interna para resolver o fluxo padrão a partir do model.

Crie `Repository` quando precisar personalizar consultas. Crie `Service` quando precisar personalizar regra de negócio.

## 🛡️ Módulo ACL

O ACL possui um provider separado do provider geral do pacote. Registre-o apenas nos projetos que usam o módulo de permissões.

### 🔌 Provider do ACL

Adicione o provider em `bootstrap/providers.php`:

```php
<?php

use App\Providers\AppServiceProvider;
use Orangesix\OrangesixServiceProvider;
use Orangesix\Acl\AclServiceProvider;

return [
    AppServiceProvider::class,
    OrangesixServiceProvider::class,
    AclServiceProvider::class,
];
```

Depois limpe os caches da aplicação:

```bash
php artisan optimize:clear
```

### 📤 Publicação dos arquivos do ACL

Publique os arquivos específicos do ACL:

```bash
php artisan vendor:publish --tag=acl-config
php artisan vendor:publish --tag=acl-migrations
php artisan vendor:publish --tag=acl-seeders
```

Isso cria:

- `config/acl.php`
- migrations do ACL em `database/migrations`
- `AclSeeder.php` em `database/seeders`

Em seguida, rode as migrations e o seeder:

```bash
php artisan migrate
php artisan db:seed --class=AclSeeder
```

### ✅ Validação rápida do ACL

Para confirmar se o provider do ACL foi registrado corretamente:

```bash
php artisan tinker
```

```php
app()->bound('acl');
class_exists(\Orangesix\Acl\Facades\Acl::class);
```

Ambos devem retornar `true`.
