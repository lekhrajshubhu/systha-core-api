<?php

namespace Systha\Core\Models;


use Stripe\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\Appointment;
use Systha\Core\Models\InvoiceHead;
use Systha\Core\Models\YearClosing;
use Systha\Core\Models\PackageSubscription;
use Illuminate\Database\Eloquent\Relations\Relation;

class Payment extends Model
{

   protected $table = "payments";
   protected $guarded = [];

   protected $with = ['paymentable'];
   public static function boot()
   {
      parent::boot();
      static::saving(function ($model) {
         $year = YearClosing::where('status', 'open')->first();
         if ($year) {
            $model->year_id = $year->id;
         }
      });
      Relation::enforceMorphMap([
         'package_subscriptions' => PackageSubscription::class,
         'appointments' => Appointment::class,
      ]);

      static::creating(function ($payment) {
         if (empty($payment->payment_code)) {
            $payment->payment_code = self::generateUniquePaymentCode();
         }
      });
   }

   public function paymentable()
   {
      return $this->morphTo(null, 'table_name', 'table_id');
   }


   public static function generateUniquePaymentCode(): string
   {
      $digits = str_split('023456789'); // exclude '1'
      $letters = str_split('ABCDEFGHJKLMNPQRSTUVWXYZ'); // exclude 'I'

      do {
         // Pick 2 unique digits
         $randomDigits = [];
         $availableDigits = $digits;
         for ($i = 0; $i < 2; $i++) {
            $index = random_int(0, count($availableDigits) - 1);
            $randomDigits[] = $availableDigits[$index];
            array_splice($availableDigits, $index, 1);
         }

         // Pick 2 unique letters
         $randomLetters = [];
         $availableLetters = $letters;
         for ($i = 0; $i < 2; $i++) {
            $index = random_int(0, count($availableLetters) - 1);
            $randomLetters[] = $availableLetters[$index];
            array_splice($availableLetters, $index, 1);
         }

         // Combine and shuffle
         $codeArray = array_merge($randomDigits, $randomLetters);
         shuffle($codeArray);

         $code = 'PAY-' . implode('', $codeArray);

         $exists = DB::table('payments')->where('payment_code', $code)->exists();
      } while ($exists);

      return $code;
   }

   public function appointment()
   {
      return $this->belongsTo(Appointment::class, 'appointment_id');
   }

   public function creator()
   {
      return $this->belongsTo(User::class, 'userc_id', 'id')->where('is_deleted', 0);
   }

   public function invoice()
   {
      return $this->belongsTo(InvoiceHead::class, 'invoice_id', 'id');
   }

   //    public function order()
   //    {
   //       return $this->belongsTo(Order::class, 'table_id', 'id')->where('table_name', 'orders');
   //    }
}
