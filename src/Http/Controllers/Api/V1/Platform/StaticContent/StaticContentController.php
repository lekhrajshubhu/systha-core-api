<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\StaticContent;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Systha\Core\Http\Resources\StaticPageResource;
use Systha\Core\Models\StaticContent;

/**
 * @group Platform
<<<<<<< HEAD
 * @subgroup Static Content
=======
 * @subgroup Inquiries
>>>>>>> 18539635f4a2a7c24ea1f527231dffef47b3d97a
 */
class StaticContentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'page' => 'required|string',
            ]);
            $staticContent = StaticContent::query()
                ->where(['is_deleted' => 0])
                ->whereNull('vendor_id')
                ->where('page_code', $validated['page'])
                ->orderByDesc('id')
                ->first();
            if (!$staticContent) {
                return response(['error' => 'Static content not found.'], 404);
            }

            return response([
                'data' => (new StaticPageResource($staticContent))->resolve($request),
            ]);
        } catch (ValidationException $th) {
            throw $th;
        } catch (\Throwable $th) {
            return response(['error' => 'Unable to fetch static content.'], 422);
        }
    }
}
