<?php

namespace Orangesix\Enum\Response;

enum Field: string
{
    case Valid = 'is-valid';
    case Invalid = 'is-invalid';
}
