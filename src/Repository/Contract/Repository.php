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
     * @param array $data
     * @return int
     */
    public function save(array $data): int;

    /**
     * @param int $id
     * @return void
     */
    public function remove(int $id): void;

    /**
     * @param int $id
     * @return mixed
     */
    public function find(int $id): mixed;
}
