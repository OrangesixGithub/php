<?php

namespace Orangesix\Repository\Core;

use Illuminate\Database\Eloquent\Model;

trait RepositoryDataBase
{
    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param mixed ...$paramns
     * @return mixed
     */
    public function find(int $id): mixed
    {
        return $this->model::query()
            ->withoutGlobalScopes()
            ->findOrFail($id);
    }

    /**
     * @param array $data
     * @return int
     */
    public function save(array $data): int
    {
        $model = empty($data['id'])
            ? $this->model
            : $this->find($data['id']);
        foreach ($data as $key => $value) {
            $model->$key = $value;
        }
        $model->save();
        return $model->id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function remove(int $id): void
    {
        $data = $this->find($id);
        $data->delete();
    }
}
