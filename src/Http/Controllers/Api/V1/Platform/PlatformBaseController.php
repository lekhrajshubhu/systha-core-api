<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Systha\Core\Models\ClientModel;
use Tymon\JWTAuth\JWTGuard;

/**
 * @group Platform
 * @property-read JWTGuard $platformGuard
 * @property-read ?ClientModel $user
 * @property-read \Tymon\JWTAuth\Token|string|null $token
 * @property-read array $profileData
 */
class PlatformBaseController extends Controller
{
    protected JWTGuard $platformGuard;
    protected ?ClientModel $user = null;
    protected \Tymon\JWTAuth\Token|string|null $token = null;
    protected array $profileData = [];

    public function __construct()
    {
        $this->platformGuard = Auth::guard('platform');
        $this->token = $this->platformGuard->getToken();
        $this->user = $this->resolveUser();
        $this->profileData = $this->buildProfileData();
    }

    protected function resolveUser(): ?ClientModel
    {
        $authUser = $this->platformGuard->user();
        if ($authUser instanceof ClientModel) {
            return $authUser;
        }

        if (! $this->token) {
            return null;
        }

        $userId = $this->platformGuard->payload()->get('sub');

        return ClientModel::where('is_deleted', 0)->find($userId);
    }

    protected function refreshTokenAndUser(): array
    {
        $this->token = $this->platformGuard->refresh();
        $this->platformGuard->setToken($this->token);
        $this->user = $this->resolveUser();
        $this->profileData = $this->buildProfileData();

        return [
            'token' => $this->token,
            'user' => $this->user,
        ];
    }

    protected function buildProfileData(): array
    {
        $user = $this->user;
        if (! $user) {
            return [];
        }

        $user->loadMissing(['address', 'stripeProfile', 'vendor']);

        return [
            'fname' => $user->fname,
            'lname' => $user->lname,
            'email' => $user->email,
            'phone' => $user->phone_no,
            'avatar' => $user->avatar,
            'address' => [
                'add1' => $user->address?->add1,
                'city' => $user->address?->city,
                'state' => $user->address?->state,
                'zip' => $user->address?->zip,
            ],
            'stripe_profile' => [
                'default_payment_method_id' => $user->stripeProfile?->default_payment_method_id,
                'stripe_customer_id' => $user->stripeProfile?->stripe_customer_id,
                'name' => $user->stripeProfile?->name,
                'email' => $user->stripeProfile?->email,
                'phone' => $user->stripeProfile?->phone,
            ],
            'vendor' => [
                'id' => $user->vendor?->id,
                'name' => $user->vendor?->name,
                'code' => $user->vendor?->vendor_code,
            ],
        ];
    }
}
