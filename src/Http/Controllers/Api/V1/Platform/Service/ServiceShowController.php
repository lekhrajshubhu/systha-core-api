<?php

/**
 * THIS INTELLECTUAL PROPERTY IS COPYRIGHT Ⓒ 2020
 * SYSTHA TECH LLC. ALL RIGHT RESERVED
 * -----------------------------------------------------------
 * SALES@SYSTHATECH.COM 
 * 512 903 2202
 * WWW.SYSTHATECH.COM
 * -----------------------------------------------------------
 */

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Systha\Core\Models\Service;

/**
 * @group Platform
 * @subgroup Messages
 */
class ServiceShowController extends Controller
{
 
    public function getDetail($id)
    {
        $service = Service::with(['vendor'])
            ->where('id', $id)
            ->first();

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        return response()->json($service);
    }
}
