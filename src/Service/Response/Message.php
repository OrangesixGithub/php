<?php

namespace Orangesix\Service\Response;

use Orangesix\Enum\Response\Message as MessageEnum;

class Message
{
    public function __construct(
        public string      $message,
        public MessageEnum $type = MessageEnum::Success,
        public ?string     $icon = null
    ) {
        if (empty($icon)) {
            $this->icon = match ($type) {
                MessageEnum::Error => 'bi bi-bug',
                MessageEnum::Success => 'bi bi-check-circle',
                MessageEnum::Warning => 'bi bi-exclamation-triangle',
                default => 'bi bi-info-circle',
            };
        }
    }
}
