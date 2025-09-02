<?php

namespace Orangesix\Enum\Response;

enum Message: string
{
    case Success = 'success';
    case Warning = 'warning';
    case Error = 'error';
    case Info = 'info';
}
