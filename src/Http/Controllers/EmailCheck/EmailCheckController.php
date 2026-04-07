<?php

namespace Systha\Core\Http\Controllers\EmailCheck;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Systha\Core\Models\Contact;

class EmailCheckController extends Controller
{

    public function check(Request $request){

        // dd($request->email);
        try {
            $contact = Contact::where([
                "email" => $request->email,
                "contact_type" => "customer"
            ])->first();
            return response(["data"=>$contact, "exists"=> $contact?1:0],200);
        } catch (\Throwable $th) {
            return response(["data"=>$th->getMessage()],422);
        }

    }
}
