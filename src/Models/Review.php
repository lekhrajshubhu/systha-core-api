<?php

namespace Systha\Core\Models;

use App\User;
use Systha\Core\Models\Client;
use Systha\Core\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Systha\systhaecommerce\Models\ReviewReply;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{

    protected $guarded = [];
    public static function boot()
    {
        parent::boot();
        Relation::enforceMorphMap([
            'vendors' => Vendor::class,
            'users' => User::class,
            'clients' => Client::class,
        ]);
    }

    public function getMorphClass()
    {
        return 'reviews';
    }

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
    public function reviewer()
    {
        return $this->morphTo('reviewer', 'reviewer_table_name', 'reviewer_table_id');
    }

    public function images()
    {
        return $this->morphMany(EcommFile::class, 'ecomm_fileable', 'table_name', 'table_id')->where('is_deleted', 0);
    }

    public function ratings()
    {
        return $this->hasMany(ReviewRating::class)->where('is_deleted', 0);
    }

    // public function allLikes()
    // {
    //     return $this->hasMany(ReviewLikes::class)->where('is_deleted', 0);
    // }

    public function helpfuls()
    {
        return $this->hasMany(Helpful::class, 'review_id', 'id')->where('is_deleted', 0);
    }

    public function unhelpfuls()
    {
        return $this->hasMany(Unhelpful::class)->where('is_deleted', 0);
    }

    public function allUpvotes()
    {
        return $this->hasMany(ReviewUpvote::class, 'review_id', 'id');
    }


    public function isLikedBy(string $table, int $id)
    {
        $isLiked = $this->allLikes()->where('liker_table_name', $table)->where('liker_table_id', $id)->first();
        if ($isLiked) {
            return true;
        }
        return false;
    }

    public function isUpvotedBy(string $table, int $id)
    {
        $isUpvoted = $this->allUpvotes()->where(['upvoter_table_name' => $table, 'upvoter_table_id' => $id])->first();
        if ($isUpvoted) {
            return true;
        }
        return false;
    }
}
