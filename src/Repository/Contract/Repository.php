<?php

namespace Orangesix\Repository\Contract;

use Illuminate\Database\Eloquent\Model;

interface Repository
{
    /**
     * @return Model
     */
    public function getModel(): Model;

    /**
     * @param mixed $paramns
     * @return mixed
     */
    public function find(mixed ...$paramns): mixed;

    /**
     * @param array $data
     * @return int
     */
    public function save(array $data): int;

    /**
     * @param int $id
     * @return void
     */
    public function remove(int $id): void;
}
