<?php

namespace Orangesix\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Orangesix\Repository\Contract\Repository;
use Orangesix\Repository\DefaultRepository;
use Orangesix\Service\Contract\Service;
use Orangesix\Service\DefaultService;

class AutoClassResolver
{
    /**
     * Normaliza o nome base usado na convenção do pacote.
     *
     * Exemplos:
     * - UserService => User
     * - serviceUser => User
     * - repositoryItemGrupo => ItemGrupo
     * - ItemGrupoController => ItemGrupo
     *
     * @param string $class Classe, propriedade mágica ou nome base.
     * @param string|array $suffix Prefixos/sufixos que devem ser removidos.
     * @return string
     */
    public static function normalize(string $class, string|array $suffix = ''): string
    {
        $base = class_basename($class);
        foreach ((array)$suffix as $item) {
            if (!empty($item)) {
                $base = preg_replace('/' . preg_quote($item, '/') . '$/i', '', $base);
                $base = preg_replace('/^' . preg_quote($item, '/') . '/i', '', $base);
            }
        }
        return Str::studly($base);
    }

    /**
     * Procura uma classe em uma lista de diretórios usando a convenção
     * {NomeBase}{Sufixo}.
     *
     * @param string $class Nome da classe.
     * @param string $suffix Sufixo esperado, como Service, Repository ou Model.
     * @param array $paths Diretórios onde a classe deve ser procurada.
     * @return string|null Classe completa encontrada.
     */
    private static function findClass(string $class, string $suffix, array $paths): ?string
    {
        $className = self::normalize(class: $class, suffix: $suffix) . $suffix;
        return self::findNamedClass($className, $paths);
    }

    /**
     * Procura uma classe pelo nome exato em uma lista de diretórios
     *
     * @param string $className Nome exato da classe.
     * @param array $paths DiretÃ³rios onde a classe deve ser procurada.
     * @return string|null Classe completa encontrada.
     */
    private static function findNamedClass(string $className, array $paths): ?string
    {
        foreach ($paths as $path) {
            $instance = getClass($path, $className);
            if (!empty($instance)) {
                return $instance['namespace'] . '\\' . $instance['class'];
            }
        }
        return null;
    }

    /**
     * Procura o `service` seguindo os padrões de projeto.
     *
     * Os diretórios são definidos em config('orangesix.service_path') ou, por
     * padrão, em app/Services e app/Service.
     *
     * @param string $class Nome da classes de service.
     * @return string|null Classe completa do service encontrado.
     */
    public static function findService(string $class): ?string
    {
        return self::findClass(class: $class, suffix: 'Service', paths: self::paths(config: 'orangesix.service_path', default: [
            app_path('Services'),
            app_path('Service'),
        ]));
    }

    /**
     * Procura o `Rule` seguindo os padrões de projeto.
     * Exemplo: PessoaBeforeDeleteRule.
     *
     * @param string $class Nome base do recurso.
     * @param string $hook Nome do hook, como beforeDelete ou afterManager.
     * @return string|null Classe completa da rule encontrada.
     */
    public static function findServiceRule(string $class, string $hook): ?string
    {
        $className = self::normalize(class: $class, suffix: ['Service', 'Repository', 'Model'])
            . Str::studly($hook)
            . 'Rule';
        return self::findNamedClass($className, self::paths(config: 'orangesix.service_path', default: [
            app_path('Services'),
            app_path('Service'),
        ]));
    }

    /**
     * Procura o `repository` seguindo os padrões de projeto.
     *
     * Os diretórios são definidos em config('orangesix.repository_path') ou, por
     * padrão, em app/Repositories e app/Repository.
     *
     * @param string $class Nome da classe de repository.
     * @return string|null Classe completa do repository encontrado.
     */
    public static function findRepository(string $class): ?string
    {
        return self::findClass(class: $class, suffix: 'Repository', paths: self::paths(config: 'orangesix.repository_path', default: [
            app_path('Repository'),
            app_path('Repositories'),
        ]));
    }

    /**
     * Procura o `model` seguindo os padrões de projeto.
     *
     * Os diretórios são definidos em config('orangesix.model_path') ou, por
     * padrão, em app/Models e app/Model.
     *
     * @param string $class Nome da classe de model.
     * @return string|null Classe completa do model encontrado.
     */
    public static function findModel(string $class): ?string
    {
        return self::findClass(class: $class, suffix: 'Model', paths: self::paths('orangesix.model_path', [
            app_path('Model'),
            app_path('Models'),
        ]));
    }

    /**
     * Cria o service do recurso.
     *
     * Primeiro tenta resolver um service concreto da aplicação. Se não existir,
     * cria o DefaultService interno com um repository já resolvido para o mesmo
     * nome base.
     *
     * @param string $class Nome da classe de service.
     * @return Service
     * @throws BindingResolutionException
     */
    public static function makeService(string $class): Service
    {
        $service = self::findService(class: $class);
        if (!empty($service)) {
            return app()->make($service);
        }
        return app()->makeWith(DefaultService::class, [
            'repository' => self::makeRepository($class),
        ]);
    }

    /**
     * Cria o repository do recurso.
     *
     * Primeiro tenta resolver um repository concreto da aplicação. Se não
     * existir, cria o DefaultRepository interno com o model resolvido para o
     * mesmo nome base.
     *
     * @param string $class Nome da classe de repository.
     * @return Repository
     * @throws BindingResolutionException
     */
    public static function makeRepository(string $class): Repository
    {
        $repository = self::findRepository(class: $class);
        if (!empty($repository)) {
            return app()->make($repository);
        }
        $model = self::makeModel(class: $class);
        return app()->makeWith(DefaultRepository::class, [
            'model' => $model,
        ]);
    }

    /**
     * Cria o model do recurso.
     *
     * O model é a classe mínima necessária para o CRUD padrão. Se ele não for
     * encontrado, a resolução automática deve falhar com uma mensagem clara.
     *
     * @param string $class Nome da classe de model.
     * @return Model
     * @throws BindingResolutionException
     */
    public static function makeModel(string $class): Model
    {
        $model = self::findModel($class);
        if (empty($model)) {
            throw new \RuntimeException("Model {$class}Model não foi encontrado.", 500);
        }
        return app()->make($model);
    }

    /**
     * Retorna os caminhos configurados para uma camada do pacote.
     *
     * Se a configuração ainda não tiver sido publicada ou estiver vazia, usa os
     * caminhos padrão definidos pelo pacote.
     *
     * @param string $config Chave de configuração.
     * @param array $default Caminhos padrão.
     * @return array
     */
    private static function paths(string $config, array $default): array
    {
        $paths = config($config);
        return empty($paths) ? $default : (array)$paths;
    }
}
