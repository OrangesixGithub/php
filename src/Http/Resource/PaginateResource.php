<?php

namespace Orangesix\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class PaginateResource
{
    /**
     * Retorna os dados da paginação
     *
     * @param Builder|EloquentBuilder $query
     * @param string $resource
     * @param Request|null $request
     * @param callable|null $getItens
     * @param string $action
     * @return array
     */
    public static function toArray(
        Builder|EloquentBuilder $query,
        string                  $resource,
        ?Request                $request = null,
        ?callable               $getItens = null,
        string                  $action = 'findAll'
    ): array {
        if (!class_exists($resource) || !is_subclass_of($resource, JsonResource::class)) {
            throw new \InvalidArgumentException("A classe $resource deve ser do tipo de " . JsonResource::class);
        }

        $request ??= new Request();

        $columns = $request->input('columns') ?? ['*'];
        $page = (int)$request->input('page', 1);
        $perPage = (int)$request->input('elements', 15);

        /** @var class-string<JsonResource> $resource */
        $collection = $resource::collection(
            $query->paginate($perPage, $columns, 'page', $page)
        );

        $paginate = $collection->resource;
        $itens = $collection->toArray(new Request([
            'action' => $action
        ]));
        return [
            'pagination' => $paginate,
            'itens' => empty($getItens) ? $itens : $getItens($itens)
        ];
    }
}
