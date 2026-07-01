<?php

namespace Orangesix\Repository\Utils;

trait RepositoryFilterSQLInjection
{
    /**
     * Monta a busca rapida mantendo o texto pesquisado como literal no SQL.
     *
     * @param string $filterSearch
     * @param mixed $search
     * @return string
     */
    private function filterBuildSearchQuery(string $filterSearch, mixed $search): string
    {
        if ($this->filterHasUnsafeSearchValue((string)$search)) {
            return '1 = 0';
        }

        $query = str_replace('@data@', $this->filterEscapeLikeValue((string)$search), $filterSearch);
        return preg_replace(
            "/\bLIKE\s+'(?:''|[^'])*'(?!\s+ESCAPE\b)/i",
            "$0 ESCAPE '!'",
            $query
        ) ?? $query;
    }

    /**
     * Escapa caracteres que poderiam fechar a string SQL ou virar coringa do LIKE.
     *
     * @param string $value
     * @return string
     */
    private function filterEscapeLikeValue(string $value): string
    {
        $value = $this->filterEscapeSqlValue($value);

        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }

    /**
     * Bloqueia payloads que tentam sair do texto da busca e virar SQL.
     *
     * @param string $value
     * @return bool
     */
    private function filterHasUnsafeSearchValue(string $value): bool
    {
        return preg_match('/[\'";`]|--|\/\*|\*\//', $value) === 1;
    }

    /**
     * Escapa aspas em valores usados em trechos SQL montados manualmente.
     *
     * @param mixed $value
     * @return string
     */
    private function filterEscapeSqlValue(mixed $value): string
    {
        return str_replace("'", "''", (string)$value);
    }

    /**
     * Valida nomes simples de campo/tabela usados dinamicamente na query.
     *
     * @param mixed $field
     * @return bool
     */
    private function filterIsSafeSqlIdentifier(mixed $field): bool
    {
        return is_string($field) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $field) === 1;
    }

    /**
     * Normaliza a direção da ordenação.
     *
     * @param mixed $direction
     * @return string
     */
    private function filterNormalizeOrderDirection(mixed $direction): string
    {
        return strtolower((string)$direction) === 'desc' ? 'desc' : 'asc';
    }

    /**
     * Bloqueia padrões perigosos quando query raw for usado por compatibilidade.
     *
     * @param mixed $query
     * @return bool
     */
    private function filterIsSafeRawQuery(mixed $query): bool
    {
        if (!is_string($query)) {
            return false;
        }
        return preg_match('/(;|--|\/\*|\*\/|\b(union|insert|update|delete|drop|alter|truncate|create|exec)\b)/i', $query) !== 1;
    }
}
