<?php

namespace Orangesix\Service\Contract;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

interface Service
{
    /**
     * @return Model
     */
    public function getModel(): Model;

    /**
     * @param array|Request $request
     * @return mixed
     */
    public function manager(array|Request $request): mixed;

    /**
     * @param array|Request $request
     * @return void
     */
    public function delete(array|Request $request): void;

    /**
     * @param array $validation
     * @param array $data
     * @return self
     */
    public function setValidated(array $validated): self;
}
