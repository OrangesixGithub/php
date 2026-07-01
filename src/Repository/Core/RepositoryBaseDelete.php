<?php

namespace Orangesix\Repository\Core;

trait RepositoryBaseDelete
{
    /**
     * Remove um registro pelo id.
     *
     * @param int $id
     * @return void
     */
    public function remove(int $id): void
    {
        $data = $this->find($id);
        $data->delete();
    }
}
