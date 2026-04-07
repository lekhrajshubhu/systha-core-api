<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;

class StripePaymentMethod extends Model
{
  protected $guarded = [];

  protected $appends = ["stripe_customer"];

  protected $casts = [
    'is_default' => 'boolean',
    'is_deleted' => 'boolean',
    'is_active' => 'boolean',
  ];
  public function customer()
  {
    return $this->belongsTo(StripeCustomer::class, 'stripe_customer_id');
  }

  // Accessor to get customer_id attribute
  public function getStripeCustomerAttribute()
  {
    // Return related customer id or null if no relation
    return $this->customer ? $this->customer->stripe_customer_id : null;
  }
}
