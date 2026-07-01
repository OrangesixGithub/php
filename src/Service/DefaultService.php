<?php

namespace Orangesix\Service;

use Illuminate\Database\Eloquent\Model;
use Orangesix\Repository\DefaultRepository;
use Orangesix\Repository\Contract\Repository;

/**
 * Service - DEFAULT
 *
 * @property DefaultRepository | Repository $repository
 */
class DefaultService extends ServiceBase
{
    public function __construct(?Repository $repository = null)
    {
        if (empty($repository)) {
            throw new \LogicException('DefaultService é uma classe interna do pacote e não deve ser instanciado diretamente.');
        }
        parent::__construct($repository);
    }

    /**
     * Retorna o model vinculado ao service
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->repository->getModel();
    }
}
