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


namespace Systha\Core\Http\Controllers\Api\V1\ContactClient\Invoice;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Systha\Core\Models\InvoiceHead;


/**
 * @group Contacts
 * @subgroup Invoices
 */
class InvoiceController extends Controller
{
    public function index(Request $request){
        $contact = auth('contacts')->user();
        try {
            $invoices = InvoiceHead::where('client_id',$contact->table_id)->with('invoicable')->latest()->get();
            return response([
                "data"=>$invoices
            ]);
        } catch (\Throwable $th) {
            return response(["error"=>$th->getMessage()],422);
        }
    }
}
