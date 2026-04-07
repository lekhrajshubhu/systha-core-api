<?php

namespace Systha\Core\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Systha\Core\Lib\Subscription\SubscriptionPlanProduct;
use Systha\Core\Lib\Subscription\StripeSub;


/**
 * \Systha\Subscription\Model\SubscriptionCart
 *
 * @property int $id
 * @property int $subscription_id
 * @property string|null $gateway
 * @property string|null $subscription
 * @property string|null $plan_id
 * @property string|null $customer_id
 * @property string|null $card_id
 * @property string|null $location_id
 * @property string|null $remarks
 * @property int $is_active
 * @property int $is_deleted
 * @property int|null $userc_id
 * @property int|null $useru_id
 * @property int|null $userd_id
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Systha\Subscription\Model\Subscription $subscription_plan
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereUsercId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereUserdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Systha\Subscription\Model\SubscriptionCart whereUseruId($value)
 * @mixin \Eloquent
 */
class SubscriptionCart extends Model
{
    protected $guarded = [];
    protected $table = 'subscription_cart';

    public function subscription_plan()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }


    public function card()
    {
        $planProduct = new SubscriptionPlanProduct($this->gateway);
        return $planProduct->fetchCard($this);
    }

    public function cardInfo()
    {
        $stripe = new StripeSub();
        return $stripe->retrieveCart($this->customer_id, $this->card_id);
    }
}
