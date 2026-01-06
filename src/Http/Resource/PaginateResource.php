<?php

namespace Orangesix\Http\Resource;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaginateResource
{
    /**
     * Retorna os dados da paginação
     *
     * @param Builder|EloquentBuilder $query
     * @param null|string $resource
     * @param Request|null $request
     * @return array
     */
    public static function toArray(
        Builder|EloquentBuilder $query,
        ?string                 $resource = null,
        ?Request                $request = null
    ): array {
        if (!class_exists($resource) || !is_subclass_of($resource, JsonResource::class)) {
            $resource = DefaultResource::class;
        }
        $request ??= new Request();

        $columns = $request->input('columns') ?? ['*'];
        $page = (int)$request->input('page', 1);
        $perPage = (int)$request->input('elements', 15);

        /** @var class-string<JsonResource> $resource */
        $collection = $resource::collection($query->paginate($perPage, $columns, 'page', $page));

        $paginate = $collection->resource;
        $itens = $collection->toArray($request->merge(['action' => 'findAll']));
        return [
            'pagination' => $paginate,
            'itens' => $itens['data'] ?? $itens
        ];
    }
}
