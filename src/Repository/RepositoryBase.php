<?php

namespace Orangesix\Repository;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Orangesix\Core\AutoInstanceConfig;
use Orangesix\Models\Core\ModelAutoInstance;
use Orangesix\Repository\Contract\Repository;
use Orangesix\Repository\Core\RepositoryBaseCore;
use Orangesix\Repository\Core\RepositoryBaseDelete;
use Orangesix\Repository\Core\RepositoryBaseManager;
use Orangesix\Repository\Utils\RepositoryFilter;
use Orangesix\Service\Core\ServiceAutoInstance;

abstract class RepositoryBase implements Repository
{
    use RepositoryFilter;
    use RepositoryBaseCore;
    use RepositoryBaseDelete;
    use RepositoryBaseManager;
    use ModelAutoInstance;
    use ServiceAutoInstance;

    /**
     * @throws BindingResolutionException
     */
    public function __construct(
        protected ?Model $model = null
    ) {
        $this->model = empty($this->model) && !AutoInstanceConfig::model($this)
            ? $this->getClassModelAuto()
            : $this->model;
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
