<?php

namespace Orangesix\Service\Response;

use Orangesix\Enum\Response\Modal as ModalEnum;

class Modal
{
    public function __construct(
        public string    $modal,
        public ModalEnum $action = ModalEnum::Open
    ) {
    }
}
