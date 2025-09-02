<?php

namespace Orangesix\Exceptions;

use Illuminate\Http\JsonResponse;
use Orangesix\Enum\Response\Message as MessageEnum;

class Message extends \Exception
{
    /**
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        $type = match ($this->code) {
            404, 400 => MessageEnum::Warning,
            default => MessageEnum::Error,
        };
        return response()->json([
            'message' => new \Orangesix\Service\Response\Message($this->getMessage(), $type)
        ], $this->getCode());
    }
}
