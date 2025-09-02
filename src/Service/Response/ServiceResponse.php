<?php

namespace Orangesix\Service\Response;

use Orangesix\Exceptions\Field;
use Orangesix\Enum\Response\Message;

class ServiceResponse
{
    /** @var Response */
    private Response $return;

    /**
     * Exibe a message no campo parametrizado
     * @param string $field
     * @param array|string $message
     * @param int $code
     * @return void
     * @throws Field
     */
    public function responseField(string $field, array|string $message, int $code = 422): void
    {
        if (gettype($field) == 'string') {
            $message = [$message];
        }
        throw new Field(json_encode([$field => $message]), $code);
    }

    /**
     * Retorna a message na sessão da aplicação
     * @param string $message
     * @param Message $type
     * @return $this
     */
    public function responseSessionMessage(string $message, Message $type = Message::Success): ServiceResponse
    {
        session()->flash('message', $message);
        session()->flash('messageType', $type);
        return $this;
    }

    /**
     * Realiza a montagem dos dados para retorno
     * @param array $return
     * @return $this
     */
    public function responseData(array $return): ServiceResponse
    {
        $data = new Response();
        foreach ($return as $key => $value) {
            if (property_exists($data, $key)) {
                $data->$key = $value;
            }
        }
        $this->return = $data;
        return $this;
    }

    /**
     * Realiza retorno dos dados
     * @return string
     */
    public function response(): string
    {
        return collect((array)$this->return)->toJson();
    }
}
