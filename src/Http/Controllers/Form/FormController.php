<?php

namespace Systha\Core\Http\Controllers\Form;

use Illuminate\Http\Request;
use Systha\Core\Http\Controllers\BaseController;
use Systha\Core\Models\Package;
use Systha\Core\Models\ServiceCategory;

class FormController extends BaseController
{

    public function inquiryForm()
    {

        $service_categories = ServiceCategory::select('id', 'service_category_name as name', 'description as question_text')->where([

            'is_deleted' => 0,
            'vendor_id' => $this->vendor->id
        ])
            ->with(['services' => function ($query) {
                $query->whereNull('parent_id')
                    ->where('is_deleted', 0)
                    ->with('children')
                    ->select('id', 'service_name as name', 'service_category_id', 'question_text', 'price'); // ✅ SELECT only needed fields
            }])
            ->get();


        return view($this->viewPath . '::website.forms.inquiry_form', compact('service_categories'));
    }
    public function scheduleServiceForm()
    {

        $service_categories = ServiceCategory::select('id', 'service_category_name as name', 'description as question_text')->where([
   
            'is_deleted' => 0,
            'template_id' => $this->template->id
        ])
            ->with(['services' => function ($query) {
                $query->whereNull('parent_id')
                    ->where('is_deleted', 0)
                    ->with('children')
                    ->select('id', 'service_name as name', 'service_category_id', 'question_text', 'price'); // ✅ SELECT only needed fields
            }])
            ->get();


        // Check if paymentCredential and val1 exist
        $stripe_public_key = null;
        if (
            isset($this->vendor) &&
            isset($this->vendor->paymentCredential) &&
            isset($this->vendor->paymentCredential->val1)
        ) {
            $stripe_public_key = $this->vendor->paymentCredential->val1;
        }


        return view($this->viewPath . '::website.forms.schedule_service_form', compact('service_categories', 'stripe_public_key'));
    }

    public function serviceCategories()
    {
        $service_categories = ServiceCategory::select('id', 'service_category_name as name', 'description as question_text')->where([
            'is_deleted' => 0,
            'vendor_id' => $this->vendor->id
        ])
            ->with(['services' => function ($query) {
                $query->whereNull('parent_id')
                    ->where('is_deleted', 0)
                    ->with('children')
                    ->select('id', 'service_name as name', 'service_category_id', 'question_text', 'price'); // ✅ SELECT only needed fields
            }])
            ->get();
        return response([
            "data" => $service_categories,
        ], 200);
    }

    public function subscriptionPackageForm(Request $request)
    {
        $packageId = (int) $request->input('pkg');

        $package = Package::with('plans')->find($packageId);

        if (!$package) {
            abort(404, 'Package not found.');
        }

        // Check if paymentCredential and val1 exist
        $stripe_public_key = null;
        if (
            isset($this->vendor) &&
            isset($this->vendor->paymentCredential) &&
            isset($this->vendor->paymentCredential->val1)
        ) {
            $stripe_public_key = $this->vendor->paymentCredential->val1;
        }

        return view($this->viewPath . '::website.forms.package_subscription_form', compact('package', 'stripe_public_key'));
    }
}
