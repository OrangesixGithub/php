<?php

describe('Function -> all -> class.php', function () {
    test('getClass', function () {
        $path = __DIR__ . '/../../../../src/Service';
        $class = getClass($path, 'DefaultService');
        expect($class)->toBeArray();
    });

    test('getClass -> NotFound', function () {
        $path = __DIR__ . '/../../../../src/Service';
        $class = getClass($path, 'NotFound');
        expect($class)->toBeNull();
    });

    test('getNamespace', function () {
        $path = __DIR__ . '/../../../../src/Service/DefaultService.php';
        $namespace = getNamespace($path);
        expect($namespace)->toBe('Orangesix\\Service');
    });

    test('getNamespace -> NotFound', function () {
        $path = __DIR__ . '/../../../../src/Service/NotFound.php';
        $namespace = getNamespace($path);
        expect($namespace)->toBeNull();
    });
});
