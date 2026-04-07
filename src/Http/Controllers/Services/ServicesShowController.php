<?php

namespace Systha\Core\Http\Controllers\Services;

use Illuminate\Http\Request;
use Systha\Core\Models\Service;
use Systha\Core\Http\Controllers\BaseController;
use Systha\Core\Models\ServiceCategory;

class ServicesShowController extends BaseController
{
    /**
     * Display a listing of the services.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $services = Service::whereNotNull('service_name')
        ->where([
                "vendor_id"=>$this->vendor->id,
                "is_deleted"=>0
            ])
            ->get();
        $temp = view($this->viewPath.'::components.template.schedule_service',compact('services'))->render();
        if($request->has('temp')){
            $temp = view($this->viewPath.'::components.template.'.$request->temp,compact('services'))->render();
        }
        return $temp;
    }

    /**
     * Store a newly created service in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function category(Request $request){
        $categories = ServiceCategory::where([
            'is_deleted' => 0,
            'vendor_id' => $this->vendor->id,
        ])
        ->with('services')
        ->get();

        // return response(["test"=>$categories],200);
        $temp = view($this->viewPath.'::components.template.service_category',compact('categories'))->render();
        return $temp;

    }
    public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        dd($request->all());
        // Create a new service
        $service = Service::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json($service, 201);
    }

    /**
     * Display the specified service.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Find the service by ID
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        return response()->json($service, 200);
    }

    /**
     * Update the specified service in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Find the service by ID
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Update the service
        $service->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json($service, 200);
    }

    /**
     * Remove the specified service from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find the service by ID
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        // Delete the service
        $service->delete();

        return response()->json(['message' => 'Service deleted successfully'], 200);
    }
}
