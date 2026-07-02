<?php

namespace Orangesix\Core;

use Orangesix\Attributes\DisableAutoInstance;

/**
 * Lê as configurações de instância automática declaradas por attribute.
 *
 * Esta classe não cria service, repository ou model. Ela apenas verifica se a
 * classe atual foi marcada com DisableAutoInstance para decidir se o pacote deve
 * pular alguma resolução automática.
 */
class AutoInstanceConfig
{
    /**
     * Verifica se a resolução automática de repository foi desativada.
     *
     * Uso comum: service que não representa um CRUD e não precisa de repository.
     *
     * @param object|string $target Instância ou nome da classe analisada.
     * @return bool
     */
    public static function repository(object|string $target): bool
    {
        return self::attribute($target)?->repository === true;
    }

    /**
     * Verifica se a resolução automática de model foi desativada.
     *
     * Uso comum: repository de relatório/dashboard que usa query customizada e
     * não precisa de um {Nome}Model.
     *
     * @param object|string $target Instância ou nome da classe analisada.
     * @return bool
     */
    public static function model(object|string $target): bool
    {
        return self::attribute($target)?->model === true;
    }

    /**
     * Recupera o attribute DisableAutoInstance aplicado na classe.
     *
     * Se a classe não tiver o attribute ou não puder ser refletida, retorna null
     * para manter o comportamento padrão do pacote: instanciar automaticamente.
     *
     * @param object|string $target Instância ou nome da classe analisada.
     * @return DisableAutoInstance|null
     */
    private static function attribute(object|string $target): ?DisableAutoInstance
    {
        try {
            $reflection = new \ReflectionClass($target);
            $attributes = $reflection->getAttributes(DisableAutoInstance::class);

            if (empty($attributes)) {
                return null;
            }

            return $attributes[0]->newInstance();
        } catch (\ReflectionException) {
            return null;
        }
    }
}
