<?php

namespace EnesEkinci\PhpRouter;

class Request
{
    protected $body;
    protected $lastRequest;
    protected $file;
    protected $method;
    protected $ip;

    public function __construct()
    {
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function getRequestUri()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';

        if ($path === '/')
            return $path;

        $position = strpos($path, '?');

        if ($position === false)
            return rtrim($path, '/');

        $path = substr($path, 0, $position);
        return $path !== '/' ? rtrim($path, '/') : $path;
    }

    public function is()
    {
    }

    public function routeIs()
    {
    }

    public function url()
    {
    }

    public function fullUrl()
    {
    }

    public function method()
    {
        return \strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isMethod(string $method)
    {
    }

    public function header()
    {
    }

    public function hasHeader(string $header)
    {
    }

    public function bearerToken()
    {
    }

    public static function ip()
    {
    }

    public function getAcceptableContentTypes()
    {
    }

    public function accepts(array $headers)
    {
    }

    public function expectsJson()
    {
    }

    public function all()
    {
    }

    public function query()
    {
        # code...
    }

    public function getQueryParams()
    {
        return $this;
    }

    public function __get(string $property)
    {
        return $this->body[$property] ?? null;
    }

    public function only()
    {
        # code...
    }

    public function except()
    {
        # code...
    }

    public function has($parameter)
    {
        # code...
    }

    public function file(string $key)
    {
        # code...
    }

    public function files()
    {
        # code...
    }
}
