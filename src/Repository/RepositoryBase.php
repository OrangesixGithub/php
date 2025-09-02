<?php

namespace Orangesix\Repository;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Orangesix\Models\Core\ModelAutoInstance;
use Orangesix\Repository\Contract\Repository;
use Orangesix\Repository\Core\RepositoryDataBase;
use Orangesix\Repository\Utils\RepositoryFilter;
use Orangesix\Service\Core\ServiceAutoInstance;

abstract class RepositoryBase implements Repository
{
    use RepositoryFilter;
    use RepositoryDataBase;
    use ModelAutoInstance;
    use ServiceAutoInstance;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(
        protected ?Model $model = null
    ) {
        $this->model = empty($this->model) ? $this->getClassModelAuto() : $this->model;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws BindingResolutionException
     */
    public function __get(string $name)
    {
        return $this->instanceAutoService($name);
    }
}
