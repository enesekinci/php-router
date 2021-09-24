<?php

namespace EnesEkinci\PhpRouter\Middleware;

use Closure;
use EnesEkinci\PhpRouter\Request;

class Web implements Middleware
{
    public function handler(Request $request, Closure $next)
    {
        return $next($request);
    }
}
