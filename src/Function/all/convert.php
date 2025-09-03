<?php

if (!function_exists('mask')) {
    /**
     * Utilizada para aplicar uma máscara a um determinado valor.
     * @param string $value
     * @param string $mask
     * @return string
     */
    function mask(string $value, string $mask): string
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; ++$i) {
            if ($mask[$i] == '#' || (strlen($value) == strlen(str_replace([' ', '-', '.', '/', '(', ')'], '', $mask)) && $mask[$i] == '?')) {
                if (isset($value[$k])) {
                    $maskared .= $value[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= str_replace('?', '', $mask[$i]);
                }
            }
        }
        return $maskared;
    }
}

if (!function_exists('filterData')) {
    /**
     * Realiza o tratamento dos dados para ser utilizado em filtro de pesquisa
     * @param string $value
     * @param string $type
     * @param string $return
     * @param string|null $field
     * @return string|array|null
     * @throws Exception
     */
    function filterData(
        string  $value,
        string  $type = 'date' | 'text' | 'id',
        string  $return = 'SQL' | 'DATA',
        ?string $field = null
    ): string|array|null {
        $data = ['<=', '>=', '<', '>', '{}', '!=', '!%', '%', '='];
        $operation = '';
        foreach ($data as $op) {
            if (is_int(strpos($value, $op))) {
                $operation = $op;
                break;
            }
        }

        if (is_bool(strpos($value, $operation)) || empty($operation)) {
            if (empty($value)) {
                return null;
            }
            throw new Exception('É necesário informar os operadores ' . implode(', ', $data) . " na string '$value'.", 400);
        }
        $values = explode($operation, $value);
        $op = ($operation == '%' ? 'LIKE' : ($operation == '!%' ? 'NOT LIKE' : $operation));
        $qy = ($op == 'LIKE' || $op == 'NOT LIKE' ? '%' : '');

        if ($type == 'date') {
            $handleDate = function (string $date, string $return): string {
                $date = explode('/', $date);
                $date[0] = (int)substr($date[0], 0, 2);
                $date[1] = isset($date[1]) ? (int)$date[1] : ($return == 'DATA' ? date('m') : 0);
                $date[2] = isset($date[2]) ? (int)$date[2] : ($return == 'DATA' ? date('Y') : 0);
                return "{$date[2]}-{$date[1]}-{$date[0]}";
            };

            $formateDate = function (string $value, bool $lastDayMonth = false): string {
                $date = array_map('intval', explode('-', $value));
                $date[0] = empty($date[0]) ? date('Y') : $date[0];
                $date[1] = empty($date[1]) ? ($lastDayMonth ? '12' : '1') : $date[1];

                $day = new DateTime("{$date[0]}-{$date[1]}-01");
                $day->modify('last day of this month');
                $date[2] = empty($date[2]) ? (!$lastDayMonth ? '01' : $day->format('d')) : $date[2];

                return "{$date[0]}-{$date[1]}-{$date[2]}";
            };

            $values[0] = $handleDate($values[0], $return);
            if (!empty($values[1])) {
                $values[1] = $handleDate($values[1], $return);
            } else {
                unset($values[1]);
            }

            if ($return == 'DATA') {
                return [$formateDate($values[0]), empty($values[1]) ? null : $formateDate($values[1], true), $op];
            }

            if ($return == 'SQL') {
                $operation = $operation == '!%' ? '!=' : ($operation == '%' ? '=' : $operation);

                if (isset($values[1]) && $operation == '{}') {
                    return "{$field} BETWEEN '" . $formateDate($values[0]) . "' AND '" . $formateDate($values[1], true) . "'";
                }

                $operation = $operation == '{}' ? '=' : $operation;
                $date = array_map('intval', explode('-', $values[0]));
                $date = array_map(function ($item) {
                    return empty($item) ? 0 : str_pad($item, 2, '0', STR_PAD_LEFT);
                }, $date);

                //Individual
                if (empty($date[2]) && empty($date[1])) {
                    return "YEAR({$field}) {$operation} '{$date[0]}'";
                }
                if (empty($date[2]) && empty($date[0]) && !empty($date[1])) {
                    return "MONTH({$field}) {$operation} '{$date[1]}'";
                }
                if (empty($date[0]) && empty($date[1]) && !empty($date[2])) {
                    return "DAY({$field}) {$operation} '{$date[2]}'";
                }

                //dia/mes
                if (empty($date[0]) && !empty($date[1]) && !empty($date[2])) {
                    if ($operation != '=' && $operation != '!=') {
                        return "{$field} {$operation} '" . date('Y') . "-{$date[1]}-{$date[2]}'";
                    } else {
                        return "MONTH({$field}) {$operation} '{$date[1]}' AND DAY({$field}) {$operation} '{$date[2]}'";
                    }
                }

                //mes/ano
                if (!empty($date[0]) && !empty($date[1]) && empty($date[2])) {
                    if ($operation != '=' && $operation != '!=') {
                        return "{$field} {$operation} '{$date[0]}-{$date[1]}-01'";
                    } else {
                        return "YEAR({$field}) {$operation} '{$date[0]}' AND MONTH({$field}) {$operation} '{$date[1]}'";
                    }
                }

                //dia/ano
                if (!empty($date[0]) && empty($date[1]) && !empty($date[2])) {
                    if ($operation != '=' && $operation != '!=') {
                        return "{$field} {$operation} '{$date[0]}-" . date('m') . "-{$date[2]}'";
                    } else {
                        return "YEAR({$field}) {$operation} '{$date[0]}' AND DAY({$field}) {$operation} '{$date[2]}'";
                    }
                } else {
                    return "{$field} {$operation} '{$date[0]}-{$date[1]}-{$date[2]}'";
                }
            }
            return null;
        }

        if ($type == 'text' && $return == 'SQL') {
            if ($operation == '{}') {
                throw new Exception("Não é possível utilizar intervalo de dados em tipo 'TEXT'.", 400);
            }
            return "{$field} {$op} '" . ($qy . $values[0] . $qy) . "'";
        }

        if ($type == 'id' && $return == 'SQL') {
            if (($op == '=' || $op == '!=')) {
                $id = array_map('intval', explode(';', $values[0]));
                $in = implode(',', $id);
                return "{$field} " . ($op == '=' ? 'IN (' : 'NOT IN (') . "{$in})";
            } else {
                throw new Exception("Não é possível utilizar este operador para o tipo 'ID'.", 400);
            }
        }

        if ($return == 'DATA') {
            throw new Exception("Não é possível retornar os dados no formato 'DATA' em tipo '{$type}'.", 400);
        }
        return null;
    }
}
