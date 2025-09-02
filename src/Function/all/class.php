<?php

if (!function_exists('getClass')) {
    /**
     * Retorna as informações da classe
     * @param string $directory
     * @param string $instance
     * @return array|null
     */
    function getClass(string $directory, string $instance): ?array
    {
        if (is_dir($directory)) {
            $interator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            $files = new \RegexIterator($interator, '/\.php$/');
            foreach ($files as $file) {
                require_once $file->getPathname();
                $classe = pathinfo($file->getPathname(), PATHINFO_FILENAME);
                $namespace = getNamespace($file->getPathname());
                if ($classe == $instance) {
                    return [
                        'namespace' => $namespace,
                        'class' => $classe
                    ];
                }
            }
            return null;
        }
        return null;
    }
}

if (!function_exists('getNamespace')) {
    /**
     * Retorna o namespace da classe php
     * @param string $filePath
     * @return array|null
     */
    function getNamespace(string $filePath): ?string
    {
        if (!is_file($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        $namespaceRegex = '/namespace\s+([^\s;]+)/i';
        if (preg_match($namespaceRegex, $content, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
