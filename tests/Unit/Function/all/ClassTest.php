<?php

describe('Function -> all -> class.php', function () {
    test('getClass() -> Verifica os retornos possíveis da função.', function () {
        $path = __DIR__ . '/../../../../src/Service';
        expect(getClass($path, 'DefaultService'))->toBeArray()
            ->and(getClass($path, 'NotFound'))->toBeNull();
    });

    test('getNamespace() -> Verifica os retornos possíveis da função.', function () {
        $path = __DIR__ . '/../../../../src/Service/';
        expect(getNamespace($path . 'DefaultService.php'))->toBe('Orangesix\\Service')
            ->and(getNamespace($path . 'NotFound.php'))->toBeNull();
    });
});
