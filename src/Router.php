<?php

namespace EnesEkinci\PhpRouter;

use EnesEkinci\PhpRouter\Exception\MiddlewareNotFound;

final class Router
{
    public static ?self $instance = null;
    public static Request $request;
    public static Response $response;
    protected static $routes = [];
    protected static $debug;
    protected static $notFoundContent = "404 Not Found";
    protected static $middlewareGroup;
    protected static $currrentRoute;
    public static $modelBinding = ['namespace' => null, 'primaryKey' => 'id'];

    public static ?string $name = null;
    public static ?string $prefix = null;
    public static array $middleware = [];


    public function __construct(Request $request, Response $response)
    {
        self::$request = $request;
        self::$response = $response;
        if (is_null(self::$instance))
            self::$instance = $this;
    }

    public static function get(string $uri,  $callback)
    {
        $method = 'get';
        $route = new Route($uri, $method, $callback);
        self::setGroupStack($route);
        self::$routes['get'][$route->uri] = $route;
        return $route;
    }

    public static function post(string $uri, $callback)
    {
        $method = 'post';
        $route = new Route($uri, $method, $callback);
        self::setGroupStack($route);
        self::$routes['post'][$route->uri] = $route;
        return $route;
    }

    public static function put($uri, $callback)
    {
        $method = 'put';
        $route = new Route($uri, $method, $callback);
        self::setGroupStack($route);
        self::$routes['put'][$route->uri] = $route;
        return $route;
    }

    public static function delete(string $uri, $callback)
    {
        $method = 'delete';
        $route = new Route($uri, $method, $callback);
        self::setGroupStack($route);
        self::$routes['delete'][$route->uri] = $route;

        return $route;
    }

    public static function match(array $methods, string $uri, $callback)
    {
        # code...
    }

    public static function group(callable $callback)
    {
        $callback();
        self::clearGroupStack();
    }

    public static function prefix(string $prefix)
    {
        self::$prefix = $prefix;
        return self::$instance;
    }

    public static function name(string $name)
    {
        self::$name = $name;
        return self::$instance;
    }

    public static function middleware(...$middlewares)
    {
        foreach ($middlewares as $key => $middleware) {
            $isIssetKey = array_key_exists($middleware, MiddlewareGroup::$middlewares);
            if (false === $isIssetKey) {
                throw new MiddlewareNotFound("$middleware not found");
            }
            self::$middleware[] = $middleware;
        }
        self::$middleware = array_unique(self::$middleware);
        return self::$instance;
    }

    public static function redirect(string $from, string $to, int $status = 301)
    {
        $method = 'redirect';
        self::$routes[$method][$from] = [
            'from' => $from,
            'to' => $to,
            'status' => $status,
        ];
    }

    public static function route(string $name, $params = null)
    {
        #
    }

    public static function modelBindingNamespace(string $namespace, string $primaryKey = 'id')
    {
        self::$modelBinding['namespace'] = $namespace;
        self::$modelBinding['primaryKey'] = $primaryKey;
    }

    public static function debug($status = null)
    {
        if (null === $status) return self::$debug;
        if (is_bool($status))
            self::$debug = $status;
    }

    public static function current()
    {
        return self::$currrentRoute;
    }

    public static function currentRouteName()
    {
        return self::current()->name;
    }

    public static function currentRouteAction()
    {
        return self::current()->callback;
    }

    public static function getRoutes($method = null)
    {
        if ($method)
            return self::$routes[strtolower($method)] ?? [];
        return self::$routes ?? [];
    }

    public function isRouteCurrent(string $route)
    {
        $uri = self::$request->getRequestUri();

        $route = ltrim(rtrim($route, '/'), '/');
        $uri = ltrim(rtrim($uri, '/'), '/');

        $routeSegments = explode('/', $route);
        $uriSegments = explode('/', $uri);

        // dd($uri, $route, [$routeSegments, $uriSegments]);

        // if ($uri === $route || $route === '*')
        if ($uri === $route || $route === '*')
            return true;

        $isStar = $routeSegments[count($routeSegments) - 1] === '*';

        if (!$isStar && count($routeSegments) !== count($uriSegments))
            return false;

        foreach ($routeSegments as $key => $rSegment)
            if (strpos($rSegment, '{') === false && $rSegment !== $uriSegments[$key])
                return false;

        return true;
    }

    public function resolve()
    {
        $uri = self::$request->getRequestUri();

        $method = self::$request->method();

        // redirect control
        $redirectRoutes = $this->getRoutes('redirect');


        foreach ($redirectRoutes as $redirectRouteUri => $redirect) {
            if ($this->isRouteCurrent($redirectRouteUri)) {
                return self::$response->redirect($redirect['to']);
            }
        }

        // general route control
        $routes = $this->getRoutes($method);

        foreach ($routes as $routeUri => $route) {
            if ($this->isRouteCurrent($routeUri)) {
                $route->isCurrent = true;
                self::$currrentRoute = $route;
                return $this->callRoute($route);
            }
        }




        return self::notFondPage();
    }

    public function callRoute(Route $route)
    {
        $arguments = $route->getCallBackArguments();

        $callback = $route->callback;
        $middlewares = $route->middleware;

        $getCallBack = function () use ($callback, $route, $arguments) {
            if (is_array($callback)) {
                $class = new $route[0]();
                $method = $route[1];
                return call_user_func_array([$class, $method], array_values($arguments));
            }
            return $callback(...$arguments);
        };

        $handler = null;
        dd($middlewares);
        foreach (array_reverse($middlewares) as $key => $middleware) {
            $middleware = new MiddlewareGroup::$middlewares[$middleware]();
            if ($key === 0) {
                $handler = function () use ($getCallBack, $middleware) {
                    return  $middleware->handler(self::$request, $getCallBack);
                };
            } else {
                $handler = function () use ($middleware, $handler) {
                    return $middleware->handler(self::$request, $handler);
                };
            }
        }
        return $handler();
    }

    public static function setNotFoundContent($callback)
    {
        self::$notFoundContent =  $callback;
    }

    protected static function notFondPage()
    {
        self::$response->status(404);

        if (is_string(self::$notFoundContent))
            echo self::$notFoundContent;

        if (is_array(self::$notFoundContent)) {
            $className = new self::$notFoundContent[0]();
            return $className->{self::$notFoundContent[1]}();
        }

        if (is_callable(self::$notFoundContent))
            return (self::$notFoundContent)();
    }

    protected static function setGroupStack(Route $route)
    {
        $route->name = self::$name . $route->name;
        $route->groupName = self::$name;
        $route->uri = self::$prefix . $route->uri;

        if (self::$middleware)
            $route->middleware(...self::$middleware);
    }

    protected static function clearGroupStack()
    {
        self::$prefix = null;
        self::$name = null;
        self::$middleware = [];
    }
}
