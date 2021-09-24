<?php

namespace EnesEkinci\PhpRouter\Middleware;

use Closure;
use EnesEkinci\PhpRouter\Request;

interface Middleware
{
    public function handler(Request $request, Closure $next);
}
