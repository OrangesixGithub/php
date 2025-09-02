<?php

namespace Orangesix\Repository;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository - DEFAULT
 */
class DefaultRepository extends RepositoryBase
{
    /**
     * @var array
     */
    private array $filters = [];

    /**
     * @var array
     */
    private array $fields = [];

    /**
     * @var array
     */
    private array $query = [];

    /**
     * @param Model|null $model
     * @return void
     */
    public function setModel(?Model $model): void
    {
        $this->model = $model;
    }

    /**
     * @param mixed $field
     * @return void
     */
    public function setField(array $field): void
    {
        $this->fields = $field;
    }

    /**
     * @param array $queryRaw
     * @return void
     */
    public function setQuery(array $queryRaw): void
    {
        $this->query = $queryRaw;
    }

    /**
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function setFilter(string $name, callable $callback): void
    {
        $this->filters[$name] = $callback;
    }

    /**
     * @param Request|null $request
     * @param string $exec
     * @return Builder|Collection
     */
    public function findAll(?Request $request = null, string $exec = 'paginate' | 'get'): Builder|Collection
    {
        $table = $this->getModel()->getTable();
        $filter = $this->filter($request);

        $query = $this->model::query()
            ->select(array_merge(["{$table}.*"], $this->fields));

        if (!empty($this->query)) {
            foreach ($this->query as $key => $value) {
                if ($key == 'join') {
                    $query = $query->from(DB::raw("{$table} {$value}"));
                }
            }
        }

        $this->getQueryFilter($query, $filter, ['ativo' => 'asc', 'id' => 'desc']);
        if ($exec == 'get') {
            return $query->get();
        }
        return $query;
    }

    /*
    |--------------------------------------------------------
    | Métodos privados
    |--------------------------------------------------------
    | Utilizados para executar regras específicas na gestão
    | das pessoas.
    |
    */

    /**
     * @param Request|array|null $request
     * @return array
     */
    private function filter(Request|array|null $request): array
    {
        $filtered = $this->getQueryFilterToArray($request);

        $query = [];

        if (!empty($this->filters)) {
            foreach ($this->filters as $name => $callback) {
                $callback($filtered, $query);
            }
        }

        return array_merge($filtered, ['query' => $query ?? []], ['order' => $this->getQueryFilterOrder($filtered)]);
    }
}
