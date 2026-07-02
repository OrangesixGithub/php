<?php

namespace Orangesix\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class DisableAutoInstance
{
    public function __construct(
        public bool $repository = false,
        public bool $model = false
    ) {
    }
}
