<?php

namespace Systha\Core\Models;

use Systha\Core\Traits\StoreAudit;
use Illuminate\Database\Eloquent\Model;

class FrontendMenu extends Model
{
   use StoreAudit;
   protected $guarded = [];

   // protected $with = ['category'];
   public function detail()
   {
      return $this->belongsTo(FrontendMenu::class, 'detailpage_link', 'id');
   }
   // Accessor to return detail->menu_code as detail_link
   public function getDetailLinkAttribute()
   {
      return $this->detail ? $this->detail->menu_code : null;
   }


   public function vendor()
   {
      return $this->belongsTo(Vendor::class, 'vendor_id', 'id')->where('is_deleted', 0);
   }

   public function parent()
   {
      return $this->belongsTo(FrontendMenu::class, 'parent_id', 'id')->where('is_deleted', 0);
   }

   public function subMenus()
   {
      return $this->hasMany(self::class, 'parent_id', 'id')->where('is_deleted', 0)->orderBy('seq_no', "ASC");
   }

   public function vendorTemplate()
   {
      return $this->belongsTo(VendorTemplate::class, 'vendor_template_id', 'id')->where('is_deleted', 0);
   }

   public function category()
   {
      return $this->belongsTo(Category::class, 'category_id', 'id')->where('is_deleted', 0);
   }
   public function service()
   {
      return $this->belongsTo(Service::class, 'service_id', 'id');
   }

   public function serviceCategory()
   {
      return $this->belongsTo(ServiceCategory::class, 'service_category_id', 'id');
   }


   public function frontendMenuContent()
   {
      return $this->hasOne(StaticContent::class, 'frontend_menu_id', 'id')->where('is_deleted', 0);
   }

   public function content()
   {
      return $this->hasOne(StaticContent::class, 'frontend_menu_id', 'id')->where('is_deleted', 0);
   }

   public function displayImage()
   {
      return $this->morphOne(EcommFile::class, 'ecomm_fileable', 'table_name', 'table_id')->where('is_deleted', 0);
   }

   public function menuImage()
   {
      return $this->hasOne(EcommFile::class, 'table_id', 'id')->where('table_name', 'frontend_menus')->where('is_deleted', 0);
   }

   public function components()
   {
      return $this->hasMany(VendorMenuComponent::class, 'page_id', 'id')->orderBy('seq_no', "ASC");
   }
}
