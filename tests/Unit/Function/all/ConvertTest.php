<?php

describe('Function -> all -> convert.php', function () {
    test('mask() -> Verifica os retornos possíveis da função.', function () {
        expect(mask('12251703624', '###.###.###-##'))->toBe('122.517.036-24')
            ->and(mask('47531169000131', '##.###.###/####-##'))->toBe('47.531.169/0001-31')
            ->and(mask('35991864593', '(##) ?####-####'))->toBe('(35) 99186-4593')
            ->and(mask('3532228080', '(##) ?####-####'))->toBe('(35) 3222-8080');
    });

    test('filterData() -> Retorna o filtro TEXT', function () {
        expect(filterData('terms=', 'text', 'SQL', 'field'))->toBe("field = 'terms'")
            ->and(filterData('terms!=', 'text', 'SQL', 'field'))->toBe("field != 'terms'")
            ->and(filterData('terms>=', 'text', 'SQL', 'field'))->toBe("field >= 'terms'")
            ->and(filterData('terms<=', 'text', 'SQL', 'field'))->toBe("field <= 'terms'")
            ->and(filterData('terms%', 'text', 'SQL', 'field'))->toBe("field LIKE '%terms%'")
            ->and(filterData('terms!%', 'text', 'SQL', 'field'))->toBe("field NOT LIKE '%terms%'")
            ->and(fn () => filterData('terms@', 'text', 'SQL', 'field'))->toThrow(Exception::class, "É necesário informar os operadores <=, >=, <, >, {}, !=, !%, %, = na string 'terms@'.")
            ->and(fn () => filterData('terms{}terms', 'text', 'SQL', 'field'))->toThrow(Exception::class, "Não é possível utilizar intervalo de dados em tipo 'TEXT'")
            ->and(fn () => filterData('terms=', 'text', 'DATA', 'field'))->toThrow(Exception::class, "Não é possível retornar os dados no formato 'DATA' em tipo 'text'.");
    });

    test('filterData() -> Retorna o filtro ID', function () {
        expect(filterData('10;20;30=', 'id', 'SQL', 'field'))->toBe('field IN (10,20,30)')
            ->and(filterData('10;20;30!=', 'id', 'SQL', 'field'))->toBe('field NOT IN (10,20,30)')
            ->and(fn () => filterData('10;20;30{}', 'id', 'SQL', 'field'))->toThrow(Exception::class, "Não é possível utilizar este operador para o tipo 'ID'")
            ->and(fn () => filterData('10;20;30=', 'id', 'DATA', 'field'))->toThrow(Exception::class, "Não é possível retornar os dados no formato 'DATA' em tipo 'id'");
    });

    test('filterData() -> Retorna o filtro DATE', function () {
        expect(filterData('10/0/0=', 'date', 'SQL', 'field'))->toBe("DAY(field) = '10'")
            ->and(filterData('10/0/0!=', 'date', 'SQL', 'field'))->toBe("DAY(field) != '10'")
            ->and(filterData('10/10/0=', 'date', 'SQL', 'field'))->toBe("MONTH(field) = '10' AND DAY(field) = '10'")
            ->and(filterData('10/10/0!=', 'date', 'SQL', 'field'))->toBe("MONTH(field) != '10' AND DAY(field) != '10'")
            ->and(filterData('10/0/2025=', 'date', 'SQL', 'field'))->toBe("YEAR(field) = '2025' AND DAY(field) = '10'")
            ->and(filterData('10/0/2025!=', 'date', 'SQL', 'field'))->toBe("YEAR(field) != '2025' AND DAY(field) != '10'")
            ->and(filterData('0/10/2025=', 'date', 'SQL', 'field'))->toBe("YEAR(field) = '2025' AND MONTH(field) = '10'")
            ->and(filterData('2/9/2025=', 'date', 'SQL', 'field'))->toBe("field = '2025-09-02'")
            ->and(filterData('0/1/2025{}0/12/2025', 'date', 'SQL', 'field'))->toBe("field BETWEEN '2025-1-01' AND '2025-12-31'")
            ->and(filterData('0/0/2025{}0/0/2026', 'date', 'SQL', 'field'))->toBe("field BETWEEN '2025-1-01' AND '2026-12-31'")
            ->and(filterData('0/9/0{}0/10/0', 'date', 'SQL', 'field'))->toBe("field BETWEEN '" . date('Y') . "-9-01' AND '" . date('Y') . "-10-31'")
            ->and(filterData('', 'date', 'SQL', 'field'))->toBeNull()
            ->and(filterData('10/0/0=', 'date', 'DATA', 'field'))->toEqual([date('Y') . '-1-10', null, '='])
            ->and(filterData('10/0/0{}0/0/2025', 'date', 'DATA', 'field'))->toEqual([date('Y') . '-1-10', '2025-12-31', '{}']);
    });
});
