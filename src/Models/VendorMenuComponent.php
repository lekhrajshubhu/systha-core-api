<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class VendorMenuComponent extends Model
{
    use HasFactory;
    public $with = ['posts'];

    protected $guarded = [];
    protected $appends = ['mapped_data','mapped_keys'];


    public function component()
    {
        return $this->belongsTo(VendorMenuComponent::class, 'component_id', 'id')->where('is_deleted', 0);
    }

    public function getMappedKeysAttribute()
    {
        // $allowedKeys = ['title', 'sub_title', 'highlight', 'description', 'content'];
        $allowedKeys = ['title', 'sub_title', 'highlight', 'description', 'short_description', 'content', 'image', 'button', 'link_url', 'video', 'seq_no', 'content_type', 'is_publish'];
        $foundKeys = [];

        if (!empty($this->data_mapper)) {
            $pairs = explode(',', $this->data_mapper);

            foreach ($pairs as $pair) {
                $pair = trim($pair);
                $parts = explode(':', $pair, 2);

                if (count($parts) === 2) {
                    $key = strtolower(trim($parts[0]));
                    if (in_array($key, $allowedKeys) && !in_array($key, $foundKeys)) {
                        $foundKeys[] = $key;
                    }
                }
            }
        }

        return $foundKeys;
    }


    public function getMappedDataAttribute()
    {
        $mapped = [
            'title'       => null,
            'sub_title'   => null,
            'highlight'   => null,
            'description' => null,
            'content'     => null,
        ];

        if (!empty($this->data_mapper)) {
            $pairs = explode(',', $this->data_mapper);

            foreach ($pairs as $pair) {
                $pair = trim($pair);
                $parts = explode(':', $pair, 2);

                if (count($parts) === 2) {
                    $key = strtolower(trim($parts[0]));
                    $value = trim($parts[1]);

                    if (array_key_exists($key, $mapped) && $value !== '') {
                        $mapped[$key] = $value;
                    }
                }
            }
        }

        // Filter out keys with null or empty values
        return array_filter($mapped, function ($value) {
            return !is_null($value) && $value !== '';
        });
    }




    public function page()
    {
        return $this->belongsTo(FrontendMenu::class, 'page_id', 'id')->where('is_deleted', 0);
    }
    public function post()
    {
        return $this->hasMany(VendorComponentPost::class, 'component_id', 'id')->where(['is_deleted' => 0, 'is_publish' => 0]);
    }

    // public function posts()
    // {
    //      return $this->hasMany(VendorComponentPost::class, 'component_id', 'id')->where(['is_deleted'=> 0])->orderBy('seq_no', 'asc');
    // }
    public function posts()
    {
        return $this->hasMany(VendorComponentPost::class, 'component_id', 'id')
            ->where(['is_deleted' => 0])
            ->orderBy('seq_no', 'asc');
    }

    public function refPosts()
    {
        if ($this->ref_post) {
            $posts = VendorComponentPost::where('component_id', $this->ref_post)->get();
            return $posts;
        } else {
            return [];
        }
    }


    public function image()
    {
        return $this->hasOne(EcommFile::class, 'table_id', 'id')->where('table_name', 'vendor_component_menu')->where('is_deleted', 0);
    }
}
