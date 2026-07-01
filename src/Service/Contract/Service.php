<?php

namespace Orangesix\Service\Contract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

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
}
