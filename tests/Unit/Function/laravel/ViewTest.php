<?php

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;

describe('Function -> laravel -> view.php', function () {
    afterEach(function () {
        Request::clearResolvedInstance('request');
    });

    test('IsActiveRoute() -> Retorna ativo quando a rota atual for igual.', function () {
        Request::swap(HttpRequest::create('https://orangesix.test/dashboard'));

        expect(IsActiveRoute('https://orangesix.test/dashboard'))->toBe('active')
            ->and(IsActiveRoute('https://orangesix.test/users'))->toBe('');
    });

    test('IsActiveRoute() -> Retorna valores customizados.', function () {
        Request::swap(HttpRequest::create('https://orangesix.test/dashboard'));

        expect(IsActiveRoute('https://orangesix.test/dashboard', 'selected', 'disabled'))->toBe('selected')
            ->and(IsActiveRoute('https://orangesix.test/users', 'selected', 'disabled'))->toBe('disabled');
    });

    test('IsActiveRoute() -> Verifica lista de rotas.', function () {
        Request::swap(HttpRequest::create('https://orangesix.test/users'));

        expect(IsActiveRoute([
            'https://orangesix.test/dashboard',
            'https://orangesix.test/users',
        ]))->toBe('active')
            ->and(IsActiveRoute([
                'https://orangesix.test/dashboard',
                'https://orangesix.test/settings',
            ]))->toBe('');
    });
});
