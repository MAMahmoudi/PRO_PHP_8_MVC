<?php


//The following line shows all the settings and modules that are installed with this version of PHP.
//phpinfo();

//The following line shows the context being provided with a normal request.
//var_dump(getenv('PHP_ENV'), $_SERVER, $_REQUEST);


//The ?? syntax means if the thing on the left is null or undefined, then use the thing on the right.
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestPath = $_SERVER['REQUEST_URI'] ?? '/';

// if ($requestMethod === 'GET' and $requestPath === '/') {
//     print 'HELLO WORLD';
// } else {
//     print '404 NOT FOUND';
// }


/*
The kind of multiline string used here is called a Heredoc.
They’re not new to PHP, but what is new about them is that you can indent them.
*/

// if ($requestMethod === 'GET' and $requestPath === '/') {
//     print <<<HTML
//         <!doctype html>
//         <html lang="en">
//             <body>
//                 hello world
//             </body>
//         </html>
//     HTML;
// } else {
//     include(__DIR__ . '\..\app\views\404.view.php');
// }


/* In the following code, when a user visits the /old-home path, they’ll be redirected to the /
path. 301 means the browser should remember this as a permanent redirect. You could
use 302 if the redirect is only temporary. */


// if ($requestMethod === 'GET' and $requestPath === '/') {
//     print <<<HTML
//         <!doctype html>
//         <html lang="en">
//             <body>
//                 hello world
//             </body>
//         </html>
//     HTML;
// } else if ($requestPath === '/old-home') {
//     header('Location: /', $replace = true, $code = 301);
//     exit;
// } else {
//     include(__DIR__ . '\..\app\views\404.view.php');
// }

function redirectForeverTo($path)
{
    header("Location: {$path}", $replace = true, $code = 301);

    // exit terminate script execution. You can also use the die function to terminate script execution.
    exit;
}

// if ($requestMethod === 'GET' and $requestPath === '/') {
//     print <<<HTML
//         <!doctype html>
//         <html lang="en">
//             <body>
//                 hello world
//             </body>
//         </html>
//     HTML;
// } else if ($requestPath === '/old-home') {
//     redirectForeverTo('/');
// } else {
//     include(__DIR__ . '\..\app\views\404.view.php');
// }

/* The following code shows how to handle a server error.

There are three other common kinds of errors:
1. The URL is right, but the request method is wrong.
2. The URL and request method are right, but there’s an error in the code.
3. The URL and request method are right, but there’s an error in some other request parameter,
   like a form input value.
*/

/* To deal with the first case, we need to keep track of all possible URLs and the request
methods that are permitted for them, as follows:*/

$routes = [
    'GET' => [
        '/' => fn () => print
            <<<HTML
                <!doctype html>
                <html lang="en">
                    <body>
                        hello world
                    </body>
                </html>
            HTML,
        '/old-home' => fn () => redirectForeverTo('/'),
        '/has-server-error' => fn () => throw new Exception(),
        '/has-validation-error' => fn () => abort(400),
    ],
    'POST' => [],
    'PATCH' => [],
    'PUT' => [],
    'DELETE' => [],
    'HEAD' => [],
    '404' => fn () => include(__DIR__ . '\..\app\views\404.view.php'),
    '400' => fn () => include(__DIR__ . '\..\app\views\400.view.phpp'),
    '500' => fn () => include(__DIR__ . '\..\app\views\500.view.php'),
];

$paths = array_merge(
    array_keys($routes['GET']),
    array_keys($routes['POST']),
    array_keys($routes['PATCH']),
    array_keys($routes['PUT']),
    array_keys($routes['DELETE']),
    array_keys($routes['HEAD']),
);

function abort($code)
{
    global $routes;
    $routes[$code]();
}

set_error_handler(function () {
    abort(500);
});


set_exception_handler(function () {
    abort(500);
});

if (isset(
    $routes[$requestMethod],
    $routes[$requestMethod][$requestPath],
)) {
    $routes[$requestMethod][$requestPath]();
} else if (in_array($requestPath, $paths)) {
    abort(400);
} else {
    abort(404);
}

require_once __DIR__ . '/../vendor/autoload.php';
$router = new Framework\Routing\Router();
// we expect the routes file to return a callable
// or else this code would break
$routes = require_once __DIR__ . '/../app/routes.php';
$routes($router);
print $router->dispatch();
