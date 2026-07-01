<?php

namespace Orangesix\Repository\Core;

trait RepositoryBaseManager
{
    /**
     * Realiza o insert ou update do registro.
     *
     * @param array $data
     * @return int
     */
    public function save(array $data): int
    {
        $model = empty($data['id'])
            ? $this->getModel()->newInstance()
            : $this->find($data['id']);

        foreach ($data as $key => $value) {
            $model->$key = $value;
        }
        $model->save();
        return $model->id;
    }
}
