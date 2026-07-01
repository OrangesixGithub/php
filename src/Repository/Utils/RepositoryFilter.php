<?php

namespace Orangesix\Repository\Utils;

use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Orangesix\Repository\DefaultRepository;

trait RepositoryFilter
{
    use RepositoryFilterSQLInjection;

    /**
     * Converte o objeto de request em array
     * @param Request|array|null $request
     * @return array
     */
    public function getQueryFilterToArray(Request|array|null $request): array
    {
        if (empty($request)) {
            return [];
        }

        $filtered = collect(is_array($request) ? $request : $request->all());
        $filtered = $filtered->filter(function ($item) {
            if ($item === null) {
                return false;
            }
            if (is_string($item)) {
                return trim($item) !== '';
            }
            if (is_array($item)) {
                return !empty($item);
            }
            return true;
        })->toArray();

        unset(
            $filtered['page'],
            $filtered['elements']
        );

        $verifyKey = function (array $array, callable $callback): array {
            $newArray = [];
            foreach ($array as $key => $value) {
                $newKey = $callback($key);
                $newArray[$newKey] = $value;
            }
            return $newArray;
        };

        return $verifyKey($filtered, function ($key) {
            if (strpos($key, '.') !== false || $key == 'order' || $key == 'search') {
                return $key;
            }
            if ($this instanceof DefaultRepository) {
                return $this->getModel()->getTable() . '.' . $key;
            } else {
                return $key;
            }
        });
    }

    /**
     * Realiza a montagem da query de pesquisa com ordenação
     * @param array $filtered
     * @return string
     */
    public function getQueryFilterOrder(array $filtered): string
    {
        if (isset($filtered['order'])) {
            $field = $filtered['order']['field'] ?? '';
            $direction = $filtered['order']['value'] ?? 'asc';

            if (!$this->filterIsSafeSqlIdentifier($field)) {
                abort(400, 'Campo de ordenação inválido.');
            }
            return "{$field} {$this->filterNormalizeOrderDirection($direction)}";
        }
        return '';
    }

    /**
     * Realiza a montagem da query de pesquisa dos módulos com paginação
     * @param Builder|EloquentBuilder $query
     * @param array $filter
     * @param string|array $orderBy
     * @return void
     */
    public function getQueryFilter(Builder|EloquentBuilder &$query, array $filter, string|array $orderBy = 'id'): void
    {
        foreach ($filter as $field => $value) {
            if (is_string($value) && $field != 'order') {
                $data = explode('&', $value);
                if (count($data) == 1 && $field != 'id') {
                    $query->where($field, '=', $value);
                }
            }
            if (is_int($value) && $field != 'id') {
                $query->where($field, '=', $value);
            }
            if (is_array($value) && $field != 'query' && $field !== 'order') {
                $query->whereIn($field, $value);
            }
            if ($field == 'id') {
                $query->where('id', 'LIKE', "%{$value}%");
            }
            if (is_array($value) && $field == 'query') {
                foreach ($value as $qy) {
                    if (!$this->filterIsSafeRawQuery($qy)) {
                        abort(400, 'Filtro query inválido.');
                    }
                    $query->whereRaw($qy);
                }
            }
        }
        if (empty($filter['order'])) {
            if (is_string($orderBy)) {
                $query->orderBy($orderBy);
            } else {
                foreach ($orderBy as $key => $type) {
                    $query->orderBy($key, $type);
                }
            }
        }
        if (!empty($filter['order'])) {
            $this->filterApplyOrder($query, $filter['order']);
        }
    }

    /**
     * Aplica ordenação validada sem usar orderByRaw.
     *
     * @param Builder|EloquentBuilder $query
     * @param string|array $order
     * @return void
     */
    private function filterApplyOrder(Builder|EloquentBuilder $query, string|array $order): void
    {
        if (is_array($order)) {
            $field = $order['field'] ?? '';
            $direction = $order['value'] ?? 'asc';
        } else {
            $parts = preg_split('/\s+/', trim($order));
            if (count($parts) > 2) {
                abort(400, 'Ordenação inválida.');
            }
            $field = $parts[0] ?? '';
            $direction = $parts[1] ?? 'asc';
        }
        if (!$this->filterIsSafeSqlIdentifier($field)) {
            abort(400, 'Campo de ordenação inválido.');
        }
        $query->orderBy($field, $this->filterNormalizeOrderDirection($direction));
    }
}
