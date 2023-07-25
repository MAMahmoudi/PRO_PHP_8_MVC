<?php

namespace Framework\Routing;

use Exception;
use Throwable;

class Router
{
    protected array $routes = [];
    public function add(
        string $method,
        string $path,
        callable $handler
    ): Route {
        $route = $this->routes[] = new Route(
            $method,
            $path,
            $handler
        );
        return $route;
    }

    public function dispatch()
    {
        $paths = $this->paths();
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestPath = $_SERVER['REQUEST_URI'] ?? '/';
        // this looks through the defined routes and returns
        // the first that matches the requested method and path
        $matching = $this->match($requestMethod, $requestPath);
        if ($matching) {
            try {
                // this action could throw and exception
                // so we catch it and display the global error
                // page that we will define in the routes file
                return $matching->dispatch();
            } catch (Throwable $e) {
                return $this->dispatchError();
            }
        }
        // if the path is defined for a different method
        // we can show a unique error page for it
        if (in_array($requestPath, $paths)) {
            return $this->dispatchNotAllowed();
        }
        return $this->dispatchNotFound();
    }

    private function paths(): array
    {
        $paths = [];
        foreach ($this->routes as $route) {
            $paths[] = $route->path();
        }
        return $paths;
    }
    private function match(string $method, string $path): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($method, $path)) {
                return $route;
            }
        }
        return null;
    }

    private array $errorHandler = [];
    public function errorHandler(int $code, callable $handler)
    {
        $this->errorHandler[$code] = $handler;
    }
    public function dispatchNotAllowed()
    {
        $this->errorHandler[400] ??= fn () => "not allowed";
        return $this->errorHandler[400]();
    }
    public function dispatchNotFound()
    {
        $this->errorHandler[404] ??= fn () => "not found";
        return $this->errorHandler[404]();
    }

    public function dispatchError()
    {
        $this->errorHandler[500] ??= fn () => "server error";
        return $this->errorHandler[500]();
    }
    public function redirect($path)
    {
        header(
            "Location: {$path}",
            $replace = true,
            $code = 301
        );
        exit;
    }

    protected Route $current;
    public function current(): ?Route
    {
        return $this->current;
    }

    // $router->route('product-list', ['page' => 2])
    //We’d have to add some code to the Router:
    // ...later
    public function route(
        string $name,
        array $parameters = [],
    ): string {
        foreach ($this->routes as $route) {
            if ($route->name() === $name) {
                $finds = [];
                $replaces = [];
                foreach ($parameters as $key => $value) {
                    // one set for required parameters
                    array_push($finds, "{{$key}}");
                    array_push($replaces, $value);
                    // ...and another for optional parameters
                    array_push($finds, "{{$key}?}");
                    array_push($replaces, $value);
                }
                $path = $route->path();
                $path = str_replace($finds, $replaces, $path);
                // remove any optional parameters not provided
                $path = preg_replace('#{[^}]+}#', '', $path);
                // we should think about warning if a required
                // parameter hasn't been provided...
                return $path;
            }
        }
        throw new Exception('no route with that name');
    }
}
