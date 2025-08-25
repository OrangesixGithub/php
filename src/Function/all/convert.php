<?php

if (!function_exists('EnumToArray')) {
    /**
     * Utilitário para converter um enumerador (enum) em um array associativo com diferentes formatos.
     * @param array $enum
     * @param string|null $type
     * @return array
     */
    function EnumToArray(array $enum, ?string $type = null): array
    {
        $types = [];
        foreach ($enum as $key => $item) {
            if (empty($type)) {
                $types[] = $item;
            } elseif ($type === 'select') {
                $types[$key]['id'] = $item;
                $types[$key]['name'] = $item;
            } elseif ($type === 'radio') {
                $types[$key]['value'] = $item;
                $types[$key]['legend'] = $item;
            }
        }
        return $types;
    }
}

if (!function_exists('BuildTree')) {
    /**
     * Realiza a montagem da estrutura de dados para componente TreeList
     * @param array $enum
     * @param string|null $type
     * @return array
     */
    function BuildTree(array $data): array
    {
        $nodes = [];
        foreach ($data as $item) {
            $nodes[$item['id']] = $item;
        }
        $array = [];
        foreach ($data as $item) {
            if (isset($nodes[$item['parent']])) {
                $parentNode = &$nodes[$item['parent']];
                if (!isset($parentNode['children'])) {
                    $parentNode['children'] = [];
                }
                $parentNode['children'][] = &$nodes[$item['id']];
            } else {
                $array[] = &$nodes[$item['id']];
            }
        }
        return $array;
    }
}

if (!function_exists('BuildTreeExists')) {
    /**
     * Realiza a montagem da árvore de dados somente com os dados selecionáveis
     * @param array $data
     * @return array
     */
    function BuildTreeExists(array $data): array
    {
        $i = 0;
        while (count($data) > 0 && $i < count($data)) {
            $search = array_search($data[$i]['id'], array_column($data, 'parent'));
            if (!$search && !$data[$i]['selected']) {
                unset($data[$i]);
                $data = array_values($data);
                $i = 0;
            } else {
                $i++;
            }
        }
        return array_values($data);
    }
}

if (!function_exists('Mask')) {
    /**
     * Utilizada para aplicar uma máscara a um determinado valor.
     * @param string $value
     * @param string $mask
     * @return string
     */
    function Mask(string $value, string $mask): string
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; ++$i) {
            if ($mask[$i] == '#') {
                if (isset($value[$k])) {
                    $maskared .= $value[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }
        return $maskared;
    }
}

if (!function_exists('FilterData')) {
    /**
     * Realiza o tratamento dos dados para ser utilizado em filtro de pesquisa
     * @param string $value
     * @param string $type
     * @param string $return
     * @param string|null $field
     * @return string|array|null
     * @throws Exception
     */
    function FilterData(
        string  $value,
        string  $type = 'date' | 'text' | 'id',
        string  $return = 'SQL' | 'DATA',
        ?string $field = null
    ): string|array|null
    {
        $data = ['<=', '>=', '<', '>', '{}', '!=', '!%', '%', '='];
        $operation = '';
        foreach ($data as $op) {
            if (is_int(strpos($value, $op))) {
                $operation = $op;
                break;
            }
        }

        if (!strpos($value, $operation)) {
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
                $date[1] = empty($date[1]) ? date('m') : $date[1];

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
                return [$values[0], $values[1] ?? null, $op];
            }

            if ($return == 'SQL') {
                $operation = $operation == '!%'
                    ? '!='
                    : ($operation == '%' ? '=' : $operation);

                if (isset($values[1]) && $operation == '{}') {
                    return "{$field} BETWEEN '" . $formateDate($values[0]) . "' AND '" . $formateDate($values[1], true) . "'";
                }

                $operation = $operation == '{}' ? '=' : $operation;
                $date = array_map('intval', explode('-', $values[0]));
                $date = array_map(function ($item) {
                    return empty($item) ? 0 : str_pad($item, 2, "0", STR_PAD_LEFT);
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

        if ($type == 'text') {
            if ($return == 'DATA') {
                return [trim($values[0]), trim($values[1]) ?? null, $op];
            }

            if ($return == 'SQL') {
                if (isset($values[1]) && $operation == '{}') {
                    throw new Exception("Não é possível utilizar intervalo de dados em tipo 'TEXT'.", 400);
                }
                return "{$field} {$op} '" . ($qy . $values[0] . $qy) . "'";
            }
        }

        if ($type == 'id' && ($op == '=' || $op == '!=')) {
            $id = array_map('intval', explode(';', $values[0]));
            if ($return == 'DATA') {
                return [$id, $op];
            }
            if ($return == 'SQL') {
                $in = implode(',', $id);
                return "{$field} " . ($op == '=' ? 'IN (' : 'NOT IN (') . "{$in})";
            }
        }

        return null;
    }
}
