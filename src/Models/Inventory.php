<?php

namespace Systha\Core\Models;

use Stripe\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;
    protected $table = 'inventories';
    protected $guarded = [];
    protected $casts = [
        'variants' => 'array'
    ];
    protected $appends = ['thumbnail_url'];

    // public $with = ['product', 'thumbnail','images'];


    public function getMorphClass()
    {
        return 'inventories';
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            $folderName = $this->thumbnail->folder_name ?? 'product/inventory/thumbnail';
            $fileName = $this->thumbnail->file_name ?? '';

            if ($folderName && $fileName) {
                return url("image?p={$folderName}/{$fileName}");
            }
        }

        // Return a default placeholder image URL if thumbnail does not exist
        return url('image?p=images/noimage.webp');
    }
    public function setVariantsAttribute($value)
    {
        // Filter out empty or null values in the array (keys are preserved)
        $variants = array_filter($value, function ($array_item) {
            return !is_null($array_item) && $array_item !== "" && !empty($array_item);
        });

        // Re-index the array to ensure numeric keys are not preserved (optional)
        $this->attributes['variants'] = json_encode($variants);
    }

    public function vendor(){
        return $this->belongsTo(Vendor::class,'vendor_id')->select('id','name','profile_pic as logo');
    }


    public function product()
    {
        // return $this->belongsTo(Product::class,'product_id');
        return $this->belongsTo(Product::class, 'product_id')->select('id', 'name', 'code', 'is_deleted', 'short_desc');
    }
    public function prod()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function thumbnail()
    {
        return $this->morphOne(EcommFile::class, 'ecomm_fileable', 'table_name', 'table_id')->where(['type' => 'thumbnail', 'is_deleted' => 0]);
    }

    public function images()
    {
        return $this->morphMany(EcommFile::class, 'ecomm_fileable', 'table_name', 'table_id')->where(['type' => 'attachment', 'is_deleted' => 0]);
    }

    // public function location()
    // {
    //     return $this->belongsTo(VendorLocation::class, 'location_id', 'id');
    // }

    public function inventoryImages()
    {
        return $this->morphMany(EcommFile::class, 'ecomm_fileable', 'table_name', 'table_id')
           
            ->where(function ($query) {
                $query->where('type', 'thumbnail')
                    ->orWhere('type', 'attachment');
            })
            ->where('is_deleted', 0);
    }

    // public function tags()
    // {
    //     return $this->hasOne(InventoryTag::class, 'inventory_id', 'id')->where('is_deleted', 0);
    // }
}
