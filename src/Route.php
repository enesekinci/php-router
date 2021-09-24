<?php

namespace EnesEkinci\PhpRouter;

use EnesEkinci\PhpRouter\Exception\MiddlewareNotFound;
use ReflectionFunction;
use ReflectionMethod;

class Route
{
    public $callback;
    public ?string $name = null;
    public ?string $groupName = null;
    public string $method;
    public string $uri;
    public string $prefix;
    public bool $isCurrent = false;
    public array $middleware = [];

    public function __construct(string $uri, string $method, $callback)
    {
        if ($uri !== '/')
            $this->uri = rtrim($uri, '/');
        else
            $this->uri = $uri;

        $this->method = $method;
        $this->callback = $callback;
        $this->middleware[] = 'web';
    }

    public function getParams()
    {
        $segments = explode("/", $this->uri);

        $params = array_map(
            function ($segment) {
                if (strpos($segment, "{") !== false) {
                    return str_replace(["{", "}"], "", $segment);
                }
            },
            $segments
        );
        // return array_values(array_filter($params));
        return array_filter($params);
    }

    public function getValuesOfRouteParams()
    {
        $parameters = [];
        $segments = explode("/", Router::$request->getRequestUri());

        foreach ($this->getParams() as $key => $param) {

            if (strstr($param, ':') !== false) {

                [$className, $property] = explode(':', $param);

                $namespace = Router::$modelBinding['namespace'];

                $model = $namespace . '\\' . ucwords($className);

                if ($property == 'id') {
                    $data = @$model::findById($segments[$key]) ?? new $model() ?? null;
                } else {
                    $data = @$model::where([$property => $segments[$key]])->first() ?? new $model() ?? null;
                }

                $parameters[$className] = $data;
            } else {
                $parameters[$param] = $segments[$key];
            }
        }
        return $parameters;
    }

    public function getCallBackArguments()
    {
        $routeParamsValues = $this->getValuesOfRouteParams();

        if (is_array($this->callback)) {
            $reflectionMethod = new ReflectionMethod($this->callback[0], $this->callback[1]);
            $parameters = $reflectionMethod->getParameters();
        } elseif (is_callable($this->callback)) {
            $reflectionFunction = new ReflectionFunction($this->callback);
            $parameters = $reflectionFunction->getParameters();
        }

        $arguments = [];

        foreach ($parameters as $param) {
            $paramName = $param->getName();
            //$param is an instance of ReflectionParameter
            if ($param->getType()) {
                $paramTypeClass = $param->getType()->getName();

                $requestClassName = get_class(Router::$request);
                $responseClassName = get_class(Router::$response);

                if ($paramTypeClass === $requestClassName) {
                    $arguments[] = Router::$request;
                } else if ($paramTypeClass === $responseClassName) {
                    $arguments[] = Router::$response;
                }
            } else {
                $value = $routeParamsValues[$paramName] ?? false;
                if (false === $value)
                    if ($param->isDefaultValueAvailable()) {
                        $value = $param->getDefaultValue();
                    }
                $arguments[] = $value;
            }
        }
        return $arguments;
    }

    public function name(string $name)
    {
        $this->name = $this->groupName . $name;
        return $this;
    }

    public function middleware(...$middlewares)
    {
        foreach ($middlewares as $key => $middleware) {
            $isIssetKey = array_key_exists($middleware, MiddlewareGroup::$middlewares);
            if (false === $isIssetKey) {
                throw new MiddlewareNotFound("$middleware not found");
            }
            $this->middleware[] = $middleware;
        }
        $this->middleware = array_unique($this->middleware);
        return $this;
    }
}
