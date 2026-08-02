<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request):Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->segment(1);
        // if( in_array($lang, config('app.locales') ) )
        // {
        //     app()->setlocale($lang);
        //     return $next($request);
        // }
        if ($lang === 'en') {
            app()->setLocale('en');
            return $next($request);
        } else {
            abort(404);
        }

    }
}
