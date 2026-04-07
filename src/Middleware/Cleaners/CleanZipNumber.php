<?php

namespace Systha\Core\Middleware\Cleaners;

use Closure;

class CleanZipNumber
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
        if($request->has('delivery_zip')){
            $request['delivery_zip'] = $this->fixZip($request->delivery_zip);
        }
        else if($request->has('temporary_delivery_zip')){
            $request['temporary_delivery_zip'] = $this->fixZip($request->temporary_delivery_zip);
        }
        else if($request->has('temporary_pickup_zip')){
            $request['temporary_pickup_zip'] = $this->fixZip($request->temporary_pickup_zip);
        }
        else if($request->has('pickup_zip')){
            $request['pickup_zip'] = $this->fixZip($request->pickup_zip);
        }else if($request->has('zip')){
            $request['zip'] = $this->fixZip($request->zip);
        }else{
            return $next($request);
        }
        return $next($request);
    }

    private function fixZip($zip){
        return preg_replace('/[^0-9]/', '', $zip);
    }
}
