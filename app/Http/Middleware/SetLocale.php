<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', ['en']);
        $locale = $request->cookie('language');

        if ($locale && in_array($locale, $supported, strict: true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
