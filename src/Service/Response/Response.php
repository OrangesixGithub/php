<?php

namespace Orangesix\Service\Response;

class Response
{
    public function __construct(
        public mixed    $data = null,
        public ?Message $message = null,
        public ?Field   $field = null,
        public ?Modal   $modal = null,
        public ?string  $redirect = null,
    ) {
    }
}
