<?php

namespace Systha\Core\Middleware\Cleaners;

use Closure;

class CleanCvvNumber
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->has('code')){
            $request['code'] = preg_replace('/[^0-9]/', '', $request->code);
        }
        return $next($request);
    }
}
