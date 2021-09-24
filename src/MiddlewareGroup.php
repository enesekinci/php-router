<?php

namespace EnesEkinci\PhpRouter;

use EnesEkinci\PhpRouter\Middleware\Web;

class MiddlewareGroup
{
    public static $middlewares = [
        'web' => Web::class,
    ];

    public static function middleware(array $middlewares)
    {
        foreach ($middlewares as $mKey => $middleware) {
            static::$middlewares[$mKey] = $middleware;
        }
    }
}
