<?php

namespace Orangesix\Service\Response;

use Orangesix\Enum\Response\Field as FieldEnum;

class Field
{
    public function __construct(
        public string       $field,
        public string|array $message,
        public FieldEnum    $messageType = FieldEnum::Invalid,
        public bool         $disabled = false
    ) {
        if (gettype($message) == 'string') {
            $message = [$message];
        }
        $this->message = [$field => $message];
    }
}
