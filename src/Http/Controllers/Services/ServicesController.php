<?php

namespace Systha\Core\Http\Controllers\Services;

use Systha\Core\Http\Controllers\BaseController;
use Systha\Core\Models\FrontendMenu;
use Systha\Core\Models\VendorComponentPost;
use Systha\Core\Models\VendorMenuComponent;

class ServicesController extends BaseController
{
    public function index($dataId)
    {
        $vendorTemplate = $this->template;
        $homeMenu = FrontendMenu::where(['is_deleted' => 0, 'vendor_template_id' => $this->template->id, 'is_active' => 1, 'menu_location' => 'header', 'menu_code' => 'home'])->first();
        $currentMenu = FrontendMenu::where(['is_deleted' => 0, 'vendor_template_id' => $this->template->id, 'is_active' => 1, 'menu_location' => 'header', 'id' => $dataId])->first();
        $components = VendorMenuComponent::where('page_id', $dataId)->get();
        foreach ($components as $component) {
            if ($component->type == 'file') {
                $component->posts = VendorComponentPost::where('component_name', $component->component_name)->where('is_active', 1)->where('is_deleted', 0)->get();
            } elseif ($component->type == 'database') {
                $component->posts = VendorComponentPost::where('component_id', $component->id)->where('is_active', 1)->where('is_deleted', 0)->get();
            } else {
                $component->posts = [];
            };
        };
        $menus = FrontendMenu::where('parent_id', NULL)->where('is_deleted', 0)->where('vendor_template_id', $vendorTemplate->id)->where('is_active', 1)->where('menu_location', 'header')->orderBy('seq_no', "ASC")->get();
        $footerMenus = FrontendMenu::where(['parent_id' => NULL, 'is_deleted' => 0, 'vendor_template_id' => $this->template->id, 'menu_location' => 'footer'])->get();
        return view($this->viewPath.'::pages.page_master', compact('homeMenu', 'menus', 'footerMenus', 'currentMenu', 'components'));
    }

    public function serviceDetail(VendorComponentPost $post)
    {
        $component = $post->component()->with('posts')->first();
        $vendorTemplate = $this->template;
        $homeMenu = FrontendMenu::where(['is_deleted' => 0, 'vendor_template_id' => $this->template->id, 'is_active' => 1, 'menu_location' => 'header', 'menu_code' => 'home'])->first();
        $menus = FrontendMenu::where('parent_id', NULL)->where('is_deleted', 0)->where('vendor_template_id', $vendorTemplate->id)->where('is_active', 1)->where('menu_location', 'header')->orderBy('seq_no', "ASC")->get();
        $footerMenus = FrontendMenu::where(['parent_id' => NULL, 'is_deleted' => 0, 'vendor_template_id' => $this->template->id, 'menu_location' => 'footer'])->get();
        //$posts = VendorComponentPost::where('is_deleted', 0)->where('component_name', $post->component_name)->get();
        $posts = $component->posts;
        return view($this->viewPath.'::pages.service_detail', compact('posts', 'homeMenu', 'menus', 'footerMenus', 'post'));
    }
}
