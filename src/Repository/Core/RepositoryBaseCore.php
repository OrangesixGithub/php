<?php

namespace Orangesix\Repository\Core;

use Illuminate\Database\Eloquent\Model;

trait RepositoryBaseCore
{
    /**
     * Retorna o model vinculado ao repository.
     *
     * @return Model
     */
    public function getModel(): Model
    {
        if (!$this->model instanceof Model) {
            abort(400, 'Model não definido no repository. Use a convenção do pacote ou crie um repository específico com model válido.');
        }
        return $this->model;
    }

    /**
     * Busca um registro pelo id ignorando global scopes.
     *
     * @param int $id
     * @return Model
     */
    public function find(int $id): Model
    {
        return $this->getModel()::query()
            ->withoutGlobalScopes()
            ->findOrFail($id);
    }
}
