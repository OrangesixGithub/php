<?php

describe('Function -> date -> hours.php', function () {
    test('DecimalToHours() -> Converte decimal para horas.', function () {
        expect(DecimalToHours(1.5))->toBe('01:30')
            ->and(DecimalToHours(10))->toBe('10:00')
            ->and(DecimalToHours(0.25))->toBe('00:15');
    });

    test('HoursToDecimal() -> Converte horas para decimal.', function () {
        expect(HoursToDecimal('01:30'))->toBe(1.5)
            ->and(HoursToDecimal('02:15'))->toBe(2.25)
            ->and(HoursToDecimal('02:20', 3))->toBe(2.333)
            ->and(HoursToDecimal('0130'))->toBe(0.0);
    });

    test('HoursToMinute() -> Converte horas para minutos.', function () {
        expect(HoursToMinute('01:30'))->toBe(90)
            ->and(HoursToMinute('02:15'))->toBe(135)
            ->and(HoursToMinute('0130'))->toBe(0);
    });

    test('MinuteToHours() -> Converte minutos para horas.', function () {
        expect(MinuteToHours(90))->toBe('01:30')
            ->and(MinuteToHours(135))->toBe('02:15')
            ->and(MinuteToHours(0))->toBe('00:00');
    });

    test('SecondsToHours() -> Converte segundos para horas.', function () {
        expect(SecondsToHours(3600))->toBe('1')
            ->and(SecondsToHours(5400))->toBe('1.5');
    });
});
