<?php
 /**
 * THIS INTELLECTUAL PROPERTY IS COPYRIGHT Ⓒ 2020
 * SYSTHA TECH LLC. ALL RIGHT RESERVED
 * -----------------------------------------------------------
 * sales@systhatech.com
 * 512 903 2202
 * www.systhatech.com
 * -----------------------------------------------------------
*/
namespace Systha\Core\Middleware\Cleaners;

use Closure;

class CleanPhoneNumber
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
        if($request->has('phone')){
            $request['phone'] = preg_replace('/[^0-9]/','', $request->phone);
        }else if($request->has('temporary_phone')){
            $request['temporary_phone'] = preg_replace('/[^0-9]/','', $request->temporary_phone);
        }else if($request->has('mobile_no')){
            $request['mobile_no'] = preg_replace('/[^0-9]/','', $request->mobile_no);
        }else{
            return $next($request);
        }
        return $next($request);
    }


}
