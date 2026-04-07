<?php

namespace Systha\Core\Middleware\Cleaners;

use Closure;

class CleanCardNumber
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
        if($request->has('card')){
            $request['card'] = preg_replace('/[^0-9]/', '', $request->card);
        }
        return $next($request);
    }
}
