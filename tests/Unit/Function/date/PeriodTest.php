<?php

describe('Function -> date -> period.php', function () {
    test('SumHours() -> Soma pares de horários.', function () {
        expect(SumHours(['08:00', '12:00', '13:00', '17:30']))->toBe(8.5)
            ->and(SumHours(['08:00', '12:00', '13:00']))->toBe(0.0);
    });

    test('IsDateInRange() -> Verifica se a data está dentro do período.', function () {
        expect(IsDateInRange('2026-06-15', '2026-06-01', '2026-06-30'))->toBeTrue()
            ->and(IsDateInRange('2026-07-01', '2026-06-01', '2026-06-30'))->toBeFalse();
    });

    test('GetMonth() -> Retorna o mês por número.', function () {
        expect(GetMonth(1))->toBe('Janeiro')
            ->and(GetMonth(3))->toBe('Março')
            ->and(GetMonth(12))->toBe('Dezembro')
            ->and(GetMonth(13))->toBe('');
    });

    test('GetFeriado() -> Identifica feriados nacionais e facultativos.', function () {
        expect(GetFeriado('2026-01-01'))->toBeTrue()
            ->and(GetFeriado('2026-01-02'))->toBeFalse()
            ->and(GetFeriado('2026-01-02', ['02/01']))->toBeTrue()
            ->and(GetFeriado('data-invalida'))->toBeFalse();
    });

    test('DiasUteis() -> Conta dias úteis ignorando finais de semana e feriados.', function () {
        expect(DiasUteis('2026-01-05', '2026-01-09'))->toBe(5)
            ->and(DiasUteis('2026-01-01', '2026-01-04'))->toBe(1)
            ->and(DiasUteis('data-invalida', '2026-01-09'))->toBe(0);
    });

    test('GetDiffDate() -> Retorna diferença textual entre datas.', function () {
        expect(GetDiffDate('2026-01-01', '2026-01-03'))->toBe('há 2 dia(s)')
            ->and(GetDiffDate('2026-01-03', '2026-01-01'))->toBe('falta 2 dia(s)');
    });
});
