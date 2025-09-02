<?php

namespace Orangesix\Service\Core;

use Orangesix\Service\ServiceBase;

trait ServiceDataBaseEvent
{
    /** @var \Closure|null */
    protected ?\Closure $beforeManager = null;

    /** @var \Closure|null */
    protected ?\Closure $afterManager = null;

    /** @var \Closure|null */
    protected ?\Closure $beforeDelete = null;

    /** @var \Closure|null */
    protected ?\Closure $afterDelete = null;

    /**
     * Define uma closure para ser executada antes do 'Manager'
     * @param \Closure $closure
     * @return ServiceBase|ServiceDataBaseEvent
     */
    public function beforeManager(\Closure $closure): self
    {
        $this->beforeManager = $closure;
        return $this;
    }

    /**
     * Define uma closure para ser executada depois do 'Manager'
     * @param \Closure $closure
     * @return ServiceBase|ServiceDataBaseEvent
     */
    public function afterManager(\Closure $closure): self
    {
        $this->afterManager = $closure;
        return $this;
    }

    /**
     * Define uma closure para ser executada antes do 'Delete'
     * @param \Closure $closure
     * @return ServiceBase|ServiceDataBaseEvent
     */
    public function beforeDelete(\Closure $closure): self
    {
        $this->beforeDelete = $closure;
        return $this;
    }

    /**
     * Define uma closure para ser executada depois do 'Delete'
     * @param \Closure $closure
     * @return ServiceBase|ServiceDataBaseEvent
     */
    public function afterDelete(\Closure $closure): self
    {
        $this->afterDelete = $closure;
        return $this;
    }
}
