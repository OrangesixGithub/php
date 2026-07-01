<?php

namespace Orangesix\Service\Core;

use Orangesix\Core\AutoClassResolver;
use Orangesix\Service\DefaultService;

trait ServiceAutoInstance
{
    /**
     * Realiza a construção do objeto de serviço
     * @param string $class
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function instanceAutoService(string $class): mixed
    {
        $namespace = $this->getClassProperty($class);
        if ($namespace && $namespace !== DefaultService::class && str_contains(strtolower($namespace), 'service')) {
            return app()->make($namespace);
        }

        $classResolver = AutoClassResolver::normalize($class, 'Service');
        if (empty($classResolver) || strtolower($class) === 'service') {
            $classResolver = AutoClassResolver::normalize(static::class, ['Controller', 'Service', 'Repository']);
        }
        return AutoClassResolver::makeService($classResolver);
    }

    /**
     * Procura a classe nos comentários da classe principal atraves da anotação property
     * @param string $class
     * @return string|null
     */
    private function getClassProperty(string $class): ?string
    {
        try {
            $reflection = new \ReflectionClass($this);
            $comment = $reflection->getDocComment();

            if ($comment === false) {
                return null;
            }

            if (preg_match('/@property\s+([\\\\a-zA-Z0-9_]+)\s+\$' . $class . '/', $comment, $matchesComments)) {
                $fileContent = file_get_contents($reflection->getFileName());
                preg_match_all('/^use\s+([^;]+);/m', $fileContent, $matchesUses);

                if (empty($matchesUses[1]) && class_exists($matchesComments[1])) {
                    return $matchesComments[1];
                } else {
                    foreach ($matchesUses[1] as $use) {
                        if (str_contains($use, $matchesComments[1])) {
                            if (str_contains($use, ' as ')) {
                                $use = explode(' as ', $use)[0];
                            }
                            return class_exists($use) ? $use : null;
                        }
                    }
                }
                return class_exists($matchesComments[1]) ? $matchesComments[1] : null;
            }
            return null;
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}
