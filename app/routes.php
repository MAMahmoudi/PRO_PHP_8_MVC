<?php

use Framework\Routing\Router;

return function (Router $router) {
    $router->add(
        'GET',
        '/',
        fn () => 'hello world',
    );
    $router->add(
        'GET',
        '/old-home',
        fn () => $router->redirect('/'),
    );
    $router->add(
        'GET',
        '/has-server-error',
        fn () => throw new Exception(),
    );
    $router->add(
        'GET',
        '/has-validation-error',
        fn () => $router->dispatchNotAllowed(),
    );
};

$router->add(
    'GET',
    '/products/view/{product}',
    function () use ($router) {
        $parameters = $router->current()->parameters();
        return "product is {$parameters['product']}";
    },
);
$router->add(
    'GET',
    '/services/view/{service?}',
    function () use ($router) {
        $parameters = $router->current()->parameters();
        if (empty($parameters['service'])) {
            return 'all services';
        }
        return "service is {$parameters['service']}";
    },
);

$router->add(
    'GET',
    '/products/{page?}',
    function () use ($router) {
        $parameters = $router->current()->parameters();
        $parameters['page'] ??= 1;
        return "products for page {$parameters['page']}";
    },
)->name('product-list');


