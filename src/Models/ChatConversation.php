<?php

namespace Systha\Core\Models;



use Systha\Core\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Systha\Core\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatConversation extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['unread_vendor_count', 'unread_client_count', 'unread_provider_count', 'title', 'message_from', 'message_to'];
    public static function boot()
    {
        parent::boot();
        Relation::$morphMap["service_providers"] = ServiceProvider::class;
        Relation::morphMap([
            'enquiries' => QuoteEnq::class,
            'quote_enqs' => QuoteEnq::class,
            'orders' => Order::class,
            'appointment' => Appointment::class,
            'appointments' => Appointment::class,
            'service_providers' => ServiceProvider::class,
            'package_subscriptions' => PackageSubscription::class,
            'quotes' => Quote::class,
            'vendors' => Vendor::class,
            'clients' => Client::class,
        ]);
    }


    public function getMessageFromAttribute()
    {
        $sender = $this->getAuthenticatedMember();
        return $this->resolveMemberUser($sender);
    }
    public function getMessageToAttribute()
    {
        $authMember = $this->getAuthenticatedMember();
        $members = $this->members;

        if ($authMember) {
            $receiver = $members->first(function ($member) use ($authMember) {
                return !($member->table_name == $authMember->table_name && (int) $member->table_id === (int) $authMember->table_id);
            });
            return $this->resolveMemberUser($receiver);
        }

        // Fallback: no authenticated member context available, return first member user.
        return $this->resolveMemberUser($members->first());
    }
    public function getTitleAttribute()
    {
        if ($this->type == 'personal') {
            return $this->message_to ? $this->message_to->name : 'UNKNOWN';
        } else {
            if (!$this->conversationable) {
                return 'UNKNOWN'; // or any default value
            }
            // Switch based on the table_name of the conversationable model
            switch ($this->table_name) {
                case 'enquiries':
                case 'quote_enqs':
                    // Check if enq_no is set in the enquiry or quote enquiry models
                    return $this->conversationable->enq_no ?? 'INQUIRY';

                case 'orders':
                    // Check if order_no is set in the orders model
                    return $this->conversationable->order_no ?? 'ORDER';

                case 'appointment':
                case 'appointments':
                    // Check if appointment_no is set in the appointment model
                    return $this->conversationable->appointment_no ?? 'APPT';
                case 'quotes':
                    // Check if appointment_no is set in the appointment model
                    return $this->conversationable->quote_number ?? 'APPT';

                case 'service_providers':
                    // Ensure that the service provider name exists
                    return $this->conversationable->name ?? 'PROVIDER';

                case 'vendors':
                    // Ensure that the vendor name exists
                    return $this->conversationable->name ?? 'VENDOR';

                case 'package_subscriptions':
                    // Check if subs_no is set in the package subscriptions model
                    return $this->conversationable->subs_no ?? 'SUBSCRIPTION';

                default:
                    return 'UNKNOWN'; // Fallback for unhandled table_names
            }
        }
    }



    public function conversationable()
    {
        return $this->morphTo(__FUNCTION__, 'table_name', 'table_id');
    }

    public function getUnreadVendorCountAttribute()
    {
        return $this->messages()->where('seen_vendor', 0)->count();
    }
    public function getUnreadProviderCountAttribute()
    {
        return $this->messages()->where('seen_provider', 0)->count();
    }

    /**
     * Custom Attribute: Count of messages seen by the client
     *
     * @return int
     */
    public function getUnreadClientCountAttribute()
    {
        return $this->messages()->where('seen_client', 0)->count();
    }

    public function members()
    {
        return $this->hasMany(ChatConversationMember::class, 'conversation_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }



    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }
    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->orderBy('id', 'desc');
    }

    public function receiver()
    {
        return $this->morphTo(__FUNCTION__, 'table_name', 'table_id');
    }

    protected function getAuthenticatedMember()
    {
        $this->loadMissing('members.user');
        $identity = $this->resolveAuthenticatedIdentity();
        if (!$identity) {
            return null;
        }

        return $this->members->first(function ($member) use ($identity) {
            return $member->table_name == $identity['table_name'] && (int) $member->table_id === (int) $identity['table_id'];
        });
    }

    protected function resolveAuthenticatedIdentity()
    {
        $authContact = auth('contacts')->user();
        if ($authContact && isset($authContact->table_name, $authContact->table_id)) {
            return [
                'table_name' => $authContact->table_name,
                'table_id' => $authContact->table_id,
            ];
        }

        $client = auth('clients')->user();
        if ($client) {
            if (isset($client->contact) && $client->contact && isset($client->contact->table_name, $client->contact->table_id)) {
                return [
                    'table_name' => $client->contact->table_name,
                    'table_id' => $client->contact->table_id,
                ];
            }

            return [
                'table_name' => $client->getTable(),
                'table_id' => $client->getKey(),
            ];
        }

        $vendorClient = auth('vendor_client')->user();
        if ($vendorClient && isset($vendorClient->table_name, $vendorClient->table_id)) {
            return [
                'table_name' => $vendorClient->table_name,
                'table_id' => $vendorClient->table_id,
            ];
        }

        return null;
    }

    protected function resolveMemberUser($member)
    {
        if (!$member || !$member->user) {
            return null;
        }

        $user = $member->user;
        if (method_exists($user, 'getTable') && $user->getTable() === 'vendors') {
            $vendor = Vendor::find($member->table_id);
            if ($vendor) {
                $vendor->load('contact');
            }
            return $vendor;
        }

        return $user;
    }
}
