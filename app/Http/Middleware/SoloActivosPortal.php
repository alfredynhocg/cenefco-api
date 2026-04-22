<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloActivosPortal
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge(['soloActivos' => true]);

        return $next($request);
    }
}
