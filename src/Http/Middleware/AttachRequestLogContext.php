<?php

namespace Keysoft\HelperLibrary\Http\Middleware;

use Keysoft\HelperLibrary\Support\RequestLogContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AttachRequestLogContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = RequestLogContext::fromRequest($request);

        if ($context !== []) {
            Log::shareContext($context);
        }

        return $next($request);
    }
}
