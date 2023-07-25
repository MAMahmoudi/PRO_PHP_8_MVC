<?php

namespace Framework\Routing;

class Route
{
    protected string $method;
    protected string $path;
    protected $handler;
    public function __construct(
        string $method,
        string $path,
        callable $handler
    ) {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
    }

    public function method(string $method): string
    {
        return $this->method;
    }
    public function path(string $path): string
    {
        return $this->path;
    }

    /*
    public function matches(string $method, string $path): bool
    {
        return $this->method === $method
            && $this->path === $path;
    }*/
    public function dispatch()
    {
        return call_user_func($this->handler);
    }

    protected array $parameters = [];
    public function parameters(): array
    {
        return $this->parameters;
    }
    public function matches(string $method, string $path): bool
    {
        // if there's a literal match then don't waste
        // any more time trying to match with
        // a regular expression
        if (
            $this->method === $method
            && $this->path === $path
        ) {
            return true;
        }
        $parameterNames = [];
        // the normalisePath method ensures there's a '/'
        // before and after the path, while also
        // removing duplicate '/' characters
        //
        // examples:
        // → '' becomes '/'
        // → 'home' becomes '/home/'
        // → 'product/{id}' becomes '/product/{id}/'
        $pattern = $this->normalisePath($this->path);
        // get all the parameter names and replace them with
        // regular expression syntax, to match optional or
        // required parameters
        //
        // examples:
        // → '/home/' remains '/home/'
        // → '/product/{id}/' becomes '/product/([^/]+)/'
        // → '/blog/{slug?}/' becomes '/blog/([^/]*)(?:/?)'
        $pattern = preg_replace_callback(
            '#{([^}]+)}/#',
            function (array $found) use (&$parameterNames) {
                array_push(
                    $parameterNames,
                    rtrim($found[1], '?')
                );
                // if it's an optional parameter, we make the
                // following slash optional as well
                if (str_ends_with($found[1], '?')) {
                    return '([^/]*)(?:/?)';
                }
                return '([^/]+)/';
            },
            $pattern,
        );
        // if there are no route parameters, and it
        // wasn't a literal match, then this route
        // will never match the requested path
        if (
            !str_contains($pattern, '+')
            && !str_contains($pattern, '*')
        ) {
            return false;
        }
        preg_match_all(
            "#{$pattern}#",
            $this->normalisePath($path),
            $matches
        );
        $parameterValues = [];
        if (count($matches[1]) > 0) {
            // if the route matches the request path then
            // we need to assemble the parameters before
            // we can return true for the match
            foreach ($matches[1] as $value) {
                array_push($parameterValues, $value);
            }
            // make an empty array so that we can still
            // call array_combine with optional parameters
            // which may not have been provided
            $emptyValues = array_fill(
                0,
                count($parameterNames),
                null
            );
            // += syntax for arrays means: take values from the
            // right-hand side and only add them to the left-hand
            // side if the same key doesn't already exist.
            //
            // you'll usually want to use array_merge to combine
            // arrays, but this is an interesting use for +=
            $parameterValues += $emptyValues;
            $this->parameters = array_combine(
                $parameterNames,
                $parameterValues,
            );
            return true;
        }
        return false;
    }
    private function normalisePath(string $path): string
    {
        $path = trim($path, '/');
        $path = "/{$path}/";
        // remove multiple '/' in a row
        $path = preg_replace('/[\/]{2,}/', '/', $path);
        return $path;
    }

    protected ?string $name = null;
    public function name(string $name = null): mixed
    {
        if ($name) {
            $this->name = $name;
            return $this;
        }
        return $this->name;
    }
}
