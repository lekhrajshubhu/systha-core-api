<?php

namespace Systha\Core\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\Client;
use Systha\Core\Models\VendorClient;

/**
 * @group Tenant
 * @subgroup Profile
 */
class BaseController extends Controller
{
    protected ?VendorClient $contact = null;
    protected ?Client $client = null;
    protected ?Vendor $vendor = null;

    public function __construct()
    {
        $this->contact = Auth::guard('vendor_client')->user();

        $this->client = $this->contact?->client;
        $this->vendor = $this->contact?->vendor;
    }
}
