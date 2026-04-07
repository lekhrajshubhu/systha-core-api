<?php

namespace Systha\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class MakePackage extends Command
{
    protected $signature = 'make:package {name?} {--vendor=systha} {--force}';
    protected $description = 'Scaffold a fully independent package module (backend + default assets) under packages/{Vendor}/{Module}';

    public function handle(): int
    {
        $fs = new Filesystem;

        // 1) Module / package name (raw input)
        $nameInput = $this->argument('name') ?: $this->ask('Package module name (e.g. Admin, AdminPanel, admin-panel)');
        if (! $nameInput) {
            $this->error('Module name is required.');
            return self::FAILURE;
        }

        /*
         * Normalize module naming:
         * - Studly for PHP namespace & folder (SecondFeature)
         * - kebab-case for composer package name & routes (second-feature)
         * - lowercase no-dash for view namespace (secondfeature)
         */
        $moduleStudly = Str::studly($nameInput);           // "SecondFeature"
        $moduleSlug   = Str::kebab($moduleStudly);         // "second-feature"
        $moduleLower  = Str::lower(str_replace('-', '', $moduleSlug)); // "secondfeature"

        // 2) Vendor name
        $vendorInput = $this->option('vendor') ?: 'systha';

        /*
         * Normalize vendor naming:
         * - slug (lowercase, hyphen) for composer (systha, my-company)
         * - Studly for PHP namespace & folder (MyApp, MyCompany)
         */
        $vendorSlug   = Str::slug($vendorInput);           // "systha" / "my-company"
        $vendorStudly = Str::studly($vendorSlug);          // "Myapp" / "MyCompany"

        // Base path + namespace + asset prefix (always package)
        $basePath     = base_path("packages/{$vendorSlug}/{$moduleSlug}");
        $phpNamespace = "{$vendorStudly}\\{$moduleStudly}";
        $assetPrefix  = 'websites';

        if ($fs->exists($basePath)) {
            if (! $this->option('force')) {
                $this->error("Package module already exists at: {$basePath}");
                $this->warn('Use --force to overwrite the existing package.');
                return self::FAILURE;
            }

            $this->warn("Package already exists at {$basePath}; removing due to --force.");
            $fs->deleteDirectory($basePath);
        }

        $this->info("Creating PACKAGE module at packages/{$vendorStudly}/{$moduleStudly}");
        $this->info("Composer package name will be: {$vendorSlug}/{$moduleSlug}");

        /* -------------------------------------------------------------
         | 4) Base structure (package layout)
         * ------------------------------------------------------------- */
        $fs->makeDirectory("{$basePath}/src/Http/Controllers", 0755, true);
        $fs->makeDirectory("{$basePath}/src/Http/Controllers/Website", 0755, true);
        $fs->makeDirectory("{$basePath}/src/Http/Controllers/Auth", 0755, true);
        $fs->makeDirectory("{$basePath}/src/Console/Commands", 0755, true);
        $fs->makeDirectory("{$basePath}/routes", 0755, true);
        $fs->makeDirectory("{$basePath}/resources/views", 0755, true);
        $fs->makeDirectory("{$basePath}/resources/views/components", 0755, true);
        $fs->makeDirectory("{$basePath}/resources/views/website/layout", 0755, true);
        $fs->makeDirectory("{$basePath}/resources/assets/css", 0755, true);
        $fs->makeDirectory("{$basePath}/resources/assets/js", 0755, true);

        /* -------------------------------------------------------------
         | 5) Service Provider (package)
         * ------------------------------------------------------------- */
        $viewsPathPhp  = "__DIR__ . '/../resources/views'";

        $providerTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Systha\Core\Models\VendorTemplate;

class __MODULE_STUDLY__ServiceProvider extends ServiceProvider
{
    protected $prefix = '__MODULE_SLUG__';

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerPackageCommands();
        }

        if ($this->shouldUseRoutePrefix()) {
            Route::prefix($this->prefix)->name($this->prefix . '.')->group(__DIR__ . '/../routes/web.php');
        } else {
            $prefix = $this->prefix;
            $this->app->booted(function () use ($prefix): void {
                Route::group(['as' => $prefix . '.'], function (): void {
                    require __DIR__ . '/../routes/web.php';
                });
            });
        }

        $this->loadViewsFrom(__VIEWS_PATH__, '__MODULE_LOWER__');
    }

    protected function registerPackageCommands(): void
    {
        $commandPath = __DIR__ . '/Console/Commands';

        if (! is_dir($commandPath)) {
            return;
        }

        $commands = [];

        foreach (glob($commandPath . '/*.php') as $file) {
            $class = '__NAMESPACE__\\Console\\Commands\\' . pathinfo($file, PATHINFO_FILENAME);

            if (class_exists($class)) {
                $commands[] = $class;
            }
        }

        if (! empty($commands)) {
            $this->commands($commands);
        }
    }

    protected function shouldUseRoutePrefix(): bool
    {
        $host = request()->getHttpHost();
        $vendorTemplate = VendorTemplate::where('template_host', $host)
            ->where([
                'is_active' => 1,
                'is_deleted' => 0,
                'is_global' => 0,
                'is_default' => 1,
            ])
            ->first();

        if ($vendorTemplate && $vendorTemplate->prefix == $this->prefix) {
            return false;
        }

        return true;
    }

}
PHP;

        $provider = strtr($providerTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_LOWER__'  => $moduleLower,
            '__MODULE_SLUG__'   => $moduleSlug,
            '__VIEWS_PATH__'    => $viewsPathPhp,
        ]);

        $fs->put("{$basePath}/src/{$moduleStudly}ServiceProvider.php", $provider);

        $this->info("Service provider created: {$phpNamespace}\\{$moduleStudly}ServiceProvider");

        $buildCommandTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class __MODULE_STUDLY__BuildCommand extends Command
{
    protected $signature = '__MODULE_SLUG__:build';
    protected $description = 'Build and publish the __MODULE_STUDLY__ package assets';

    public function handle(): int
    {
        $packagePath = base_path('vendor/__VENDOR_SLUG__/__MODULE_SLUG__');
        $fs          = new Filesystem;

        if (! $fs->isDirectory($packagePath)) {
            $this->error("Package not found at {$packagePath}");
            return self::FAILURE;
        }

        $this->info('Running npm install (if needed) ...');

        if (! $this->runProcess(['npm', 'install'], $packagePath)) {
            $this->error('npm install failed for __MODULE_STUDLY__ package.');
            return self::FAILURE;
        }

        $this->info('Running npm run build ...');

        if (! $this->runProcess(['npm', 'run', 'build'], $packagePath)) {
            $this->error('npm run build failed for __MODULE_STUDLY__ package.');
            return self::FAILURE;
        }

        $this->info('Publishing Vite build to public/websites/__MODULE_SLUG__/build ...');
        Artisan::call('__MODULE_SLUG__:copy-build');

        $this->info('Copying static assets to public/websites/__MODULE_SLUG__/assets ...');
        Artisan::call('__MODULE_SLUG__:copy-assets');

        $this->info('__MODULE_STUDLY__ assets built and published successfully.');
        return self::SUCCESS;
    }

    protected function runProcess(array $command, string $cwd): bool
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && $command[0] === 'npm') {
            $command[0] = 'npm.cmd';
        }

        $process = new Process($command, $cwd);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->isSuccessful();
    }
}
PHP;

        $buildCommand = strtr($buildCommandTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_SLUG__'   => $moduleSlug,
            '__VENDOR_STUDLY__' => $vendorStudly,
            '__VENDOR_SLUG__'   => $vendorSlug,
        ]);

        $fs->put("{$basePath}/src/Console/Commands/{$moduleStudly}BuildCommand.php", $buildCommand);
        $this->info("Console build command created for {$moduleStudly}.");

        $copyBuildTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class __MODULE_STUDLY__CopyBuildCommand extends Command
{
    protected $signature = '__MODULE_SLUG__:copy-build';
    protected $description = 'Copy __MODULE_STUDLY__ build output into public/websites/__MODULE_SLUG__/build';

    public function handle(): int
    {
        $fs           = new Filesystem;
        $packagePath  = base_path('vendor/__VENDOR_SLUG__/__MODULE_SLUG__');
        $buildPath    = "{$packagePath}/resources/build";
        $publicTarget = public_path('websites/__MODULE_SLUG__/build');

        if (! $fs->isDirectory($buildPath)) {
            $this->error("Build output not found at {$buildPath}. Run __MODULE_SLUG__:build first.");
            return self::FAILURE;
        }

        if ($fs->exists($publicTarget)) {
            $fs->deleteDirectory($publicTarget);
        }

        $fs->copyDirectory($buildPath, $publicTarget);
        $this->copyManifestFiles($fs, $publicTarget);
        $this->info("__MODULE_STUDLY__ build copied to {$publicTarget}.");

        return self::SUCCESS;
    }

    protected function copyManifestFiles(Filesystem $fs, string $publicTarget): void
    {
        $manifestSource = "{$publicTarget}/.vite/manifest.json";
        $buildManifest  = "{$publicTarget}/manifest.json";
        $rootManifest   = public_path('websites/__MODULE_SLUG__/manifest.json');

        if ($fs->exists($manifestSource)) {
            $fs->copy($manifestSource, $buildManifest);
            $fs->copy($manifestSource, $rootManifest);
        } else {
            $this->warn("Vite manifest not found at {$manifestSource}. Run npm run build and re-run __MODULE_SLUG__:copy-build.");
        }
    }
}
PHP;

        $copyBuildCommand = strtr($copyBuildTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_SLUG__'   => $moduleSlug,
            '__VENDOR_STUDLY__' => $vendorStudly,
            '__VENDOR_SLUG__'   => $vendorSlug,
        ]);

        $fs->put("{$basePath}/src/Console/Commands/{$moduleStudly}CopyBuildCommand.php", $copyBuildCommand);
        $this->info("Console copy-build command created for {$moduleStudly}.");

        $copyAssetsTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class __MODULE_STUDLY__CopyAssetsCommand extends Command
{
    protected $signature = '__MODULE_SLUG__:copy-assets';
    protected $description = 'Copy __MODULE_STUDLY__ assets into public/websites/__MODULE_SLUG__/assets';

    public function handle(): int
    {
        $fs           = new Filesystem;
        $packagePath  = base_path('vendor/__VENDOR_SLUG__/__MODULE_SLUG__');
        $assetsPath   = "{$packagePath}/resources/assets";
        $publicTarget = public_path('websites/__MODULE_SLUG__/assets');

        if (! $fs->isDirectory($assetsPath)) {
            $this->error("Assets directory not found at {$assetsPath}.");
            return self::FAILURE;
        }

        if ($fs->exists($publicTarget)) {
            $fs->deleteDirectory($publicTarget);
        }

        $fs->copyDirectory($assetsPath, $publicTarget);
        $this->info("__MODULE_STUDLY__ assets copied to {$publicTarget}.");

        return self::SUCCESS;
    }
}
PHP;

        $copyAssetsCommand = strtr($copyAssetsTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_SLUG__'   => $moduleSlug,
            '__VENDOR_STUDLY__' => $vendorStudly,
            '__VENDOR_SLUG__'   => $vendorSlug,
        ]);

        $fs->put("{$basePath}/src/Console/Commands/{$moduleStudly}CopyAssetsCommand.php", $copyAssetsCommand);
        $this->info("Console copy-assets command created for {$moduleStudly}.");

        $setupCommandTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class __MODULE_STUDLY__SetupCommand extends Command
{
    protected $signature = '__MODULE_SLUG__:setup';
    protected $description = 'setup and publish the __MODULE_STUDLY__ package assets';

    public function handle(): int
    {
        $this->info('Publishing compiled build files...');
        Artisan::call('__MODULE_SLUG__:copy-build');

        $this->info('Publishing static assets...');
        Artisan::call('__MODULE_SLUG__:copy-assets');

        $this->info('Updating authentication and route configuration...');
        Artisan::call('auth:replace-config');

        $this->info('__MODULE_STUDLY__ setup completed successfully.');

        return self::SUCCESS;
    }
}
PHP;

        $setupCommand = strtr($setupCommandTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_SLUG__'   => $moduleSlug,
        ]);

        $fs->put("{$basePath}/src/Console/Commands/{$moduleStudly}SetupCommand.php", $setupCommand);
        $this->info("Console setup command created for {$moduleStudly}.");

        /* -------------------------------------------------------------
         | 6) Backend: Controllers + routes/web.php
         * ------------------------------------------------------------- */
        $baseControllerTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Systha\Core\Models\FrontendMenu;
use Systha\Core\Models\Package;
use Systha\Core\Models\Vendor;
use Systha\Core\Models\VendorDefault;
use Systha\Core\Models\VendorLookup;
use Systha\Core\Models\VendorTemplate;

class BaseController extends Controller
{

    public $template, $vendor, $viewPath, $siteSetting;
    public function __construct()
    {
        if (app()->runningInConsole()) {
            return;
        }
        $host = $this->resolveHost();
        $temp = $this->resolveTemplate($host);
        $this->template = $temp;
        $this->vendor = Vendor::find($temp->vendor_id);
        $this->viewPath = $temp->template_location ?? $temp->prefix;

        view()->share($this->buildViewData($host, $temp));
    }

    private function resolveHost()
    {
        $host = request()->getHttpHost();
        if (strpos($host, 'www.') === 0) {
            return substr($host, 4);
        }
        return $host;
    }

    private function resolveTemplate($host)
    {
        $hostColumn = Schema::hasColumn('vendor_templates', 'template_host') ? 'template_host' : 'host';

        $template = DB::table('vendor_templates')
            ->where($hostColumn, $host)
            ->where([
                'is_active' => 1,
                'is_deleted' => 0,
                'is_default' => 0,
                'prefix' => '__MODULE_SLUG__'
            ])
            ->whereNotNull('vendor_id')
            ->first();

        if (! $template) {
            $template = DB::table('vendor_templates')
                ->where($hostColumn, $host)
                ->where([
                    'is_active' => 1,
                    'is_deleted' => 0,
                    'is_default' => 1,
                ])
                ->first();
            if (! $template) {
                abort(404, "Template not found.");
            }
        }

        return $template;
    }

    private function buildViewData($host, $temp)
    {
        $googleKey = VendorDefault::where([
            "property" => "google_key_front",
            "vendor_id" => $this->vendor->id
        ])->first();
        $googleKey = $googleKey ? $googleKey->value : null;

        $headerMenus = FrontendMenu::where([
            'is_deleted' => 0,
            'vendor_template_id' => $temp->id,
        ])
            ->whereNull('parent_id')
            ->where('location_footer', 'like', '%header%')
            ->orderBy('seq_no', 'asc')
            ->with('subMenus')
            ->get();

        $footerMenus = FrontendMenu::whereNull('parent_id')
            ->where('is_deleted', 0)
            ->where('vendor_template_id', $this->template->id)
            ->where(function ($q) {
                $q->where('menu_location', 'footer')
                    ->orWhere('menu_location', 'like', 'footer,%')
                    ->orWhere('menu_location', 'like', '%,footer')
                    ->orWhere('menu_location', 'like', '%,footer,%');
            })
            ->get();

        $sociallinks = VendorDefault::whereIn('property', ['facebook', 'twitter', 'youtube', 'linked_in'])
            ->select('id', 'property', 'value')
            ->where('vendor_id', $this->vendor->id)
            ->get();

        $stripe_public_key = null;
        if (
            isset($this->vendor) &&
            isset($this->vendor->paymentCredential) &&
            isset($this->vendor->paymentCredential->val1)
        ) {
            $stripe_public_key = $this->vendor->paymentCredential->val1;
        }

        $our_services = $this->getServiceCategory();
        $quick_links = $footerMenus->filter(fn($menu) => isset($menu->service_category_id));

        $vendorDefault = VendorDefault::where('vendor_id', $this->vendor->id)
            ->select('property', 'value')
            ->get()
            ->pluck('value', 'property')
            ->toArray();
        $packages = Package::where('vendor_id', $this->vendor->id)
            ->where('is_active', 0)
            ->where('is_deleted', 0)
            ->get();
        return [
            'template' => $this->template,
            'vendor' => $this->vendor,
            "googleKey" => $googleKey,
            "host" => $host,
            "menus" => $headerMenus,
            "footerMenus" => $footerMenus,
            "social_links" => $sociallinks,
            "vendor_default" => $vendorDefault,
            "our_services" => $our_services,
            "quick_links" => $quick_links,
            "stripe_public_key" => $stripe_public_key,
            "viewPath" => $this->viewPath,
            "packages" => $packages,
        ];
    }

    public function amountFormat($amount)
    {
        return number_format($amount, 2);
    }
    public function getServiceCategory()
    {
        $categories = FrontendMenu::where('is_deleted', 0)
            ->where('vendor_template_id', $this->template->id)
            ->whereNotNull('service_category_id')
            ->get();
        return $categories;
    }
}
PHP;

        $websiteControllerTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Http\Controllers\Website;

use Systha\Core\Models\FrontendMenu;
use Systha\Core\Models\Package;
use Systha\Core\Models\Service;
use Systha\Core\Models\ServiceCategory;
use Systha\Core\Models\VendorComponentPost;

class WebsiteController extends BaseController
{
    public function page($pageCode = "home")
    {
        if (auth('webContact')->check() && $pageCode == "login") {
            return redirect('page/dashboard'); // RETURN the redirect
        }
        if (!auth('webContact')->check() && $pageCode == "dashboard") {
            return redirect('page/login'); // RETURN the redirect
        }

        $template = $this->template;
    
        $vendor = $this->vendor;
        $page = FrontendMenu::where([
            'is_deleted' => 0,
            'vendor_template_id' => $template->id,
            'is_active' => 1,
            'menu_code' => $pageCode
        ])
        ->first();
        $components = optional($page)->components ?? collect();

        $viewPath = $this->viewPath;

        return view($this->viewPath . '::website.index', compact('page', 'components', 'pageCode','viewPath'));
    }

    public function packageSubscription($slug)
    {

        $pageCode = "form-subscription";


        $page = FrontendMenu::where([
            'is_deleted' => 0,
            'vendor_template_id' => $this->template->id,
            'is_active' => 1,
            'menu_code' => $pageCode
        ])
            ->first();
        $package = Package::where([
            'slug' => $slug,
            "vendor_id" => $this->vendor->id
        ])->first();

        $components = optional($page)->components ?? collect();

            
         return view($this->viewPath . '::website.index', compact('page', 'components', 'pageCode', 'package'));
    }
    public function offerDetail($slug)
    {
        // dd($slug);

        $pageCode = "offer-detail";
        if (auth('webContact')->check() && $pageCode == "login") {
            return redirect('page/dashboard'); // RETURN the redirect
        }
        if (!auth('webContact')->check() && $pageCode == "dashboard") {
            return redirect('page/login'); // RETURN the redirect
        }

        $template = $this->template;
        $vendor = $this->vendor;
        $page = FrontendMenu::where([
            'is_deleted' => 0,
            'vendor_template_id' => $template->id,
            'is_active' => 1,
            'menu_code' => $pageCode
        ])
            ->first();

        $package = Package::where('slug', $slug)->first();
        if (!$package) {
            abort(404, "Package not found.");
        }
        $components = optional($page)->components ?? collect();

        return view($this->viewPath . '::website.index', compact('page', 'components', 'pageCode', 'package'));
    }

    public function blogDetail($slug)
    {
        $blog = VendorComponentPost::where([
            'is_deleted' => 0,
            'slug' => $slug
        ])->first();


        $page = FrontendMenu::where('menu_code', 'cleaning-tips')->first();


        return view($this->viewPath . '::website.blog', compact('blog', 'page'));
    }

    public function serviceDetail($pageCode)
    {

        $template = $this->template;
        $vendor = $this->vendor;

        // Fetch the menu
        $page = FrontendMenu::where([
            'is_deleted' => 0,
            'vendor_template_id' => $template->id,
            'is_active' => 1,
            'menu_code' => $pageCode
        ])->first();


        $service = Service::find($page->service_id);

        $components = optional($page)->components ?? collect();

        return view($this->viewPath . '::website.service-detail', compact('page', 'components', 'pageCode', 'service'));
    }



    public function pageDetail($pageCode)
    {

        $template = $this->template;
        $vendor = $this->vendor;

        // Fetch the menu
        $page = FrontendMenu::where([
            'is_deleted' => 0,
            'vendor_template_id' => $template->id,
            'is_active' => 1,
            'menu_code' => $pageCode
        ])->first();

        $components = optional($page)->components ?? collect();

        return view($this->viewPath . '::website.index', compact('page', 'components', 'pageCode'));
    }
    public function detailPage($pageCode, $id)
    {

        $template = $this->template;
        $vendor = $this->vendor;

        // Fetch the menu
        $page = FrontendMenu::where([
            'is_deleted' => 0,
            'vendor_template_id' => $template->id,
            'is_active' => 1,
            'menu_code' => $pageCode
        ])->first();



        // Get page components
        $components = optional($page)->components ?? collect();


        // Get the content dynamically
        $content = $this->getContent($pageCode, $id);
        if (!$page || $components->isEmpty()) {
            dd('Page not found or no active components available.');
        }
        // dd($content);
        return view('buglogicpc::pages.page_master', compact('page', 'components', 'pageCode', 'content'));
    }


    private function getContent($pageCode, $id)
    {

        $models = [
            'package-detail' => Package::class,
            'services' => Service::class,
            'package-checkout' => Package::class,
        ];

        if (isset($models[$pageCode])) {
            // return null;
            $content = $models[$pageCode]::find($id);
        } else {
            $content = VendorComponentPost::find($id);
        }

        // dd($content);


        if (!$content) {
            abort(404, "Requested content not found.");
        }

        return $content;
    }

    public function logo($fileName)
    {
        $path = $this->vendor->template->storage_path . '/venndors/attachments/' . $fileName;
        if (file_exists($path)) {
            return response()->file($path);
        }

        return response()->file(public_path('images/noimage.webp'));
    }

    public function serviceCategories()
    {
        $service_categories = ServiceCategory::select('id', 'service_category_name as name', 'description as question_text')->where([
            'is_deleted' => 0,
            'vendor_id' => $this->vendor->id
        ])
            ->with(['services' => function ($query) {
                $query->whereNull('parent_id')
                    ->where('is_deleted', 0)
                    ->with('children')
                    ->select('id', 'service_name as name', 'service_category_id', 'question_text', 'price'); // ✅ SELECT only needed fields
            }])
            ->get();
        return response([
            "data" => $service_categories,
        ], 200);
    }
}
PHP;

        $formVerificationTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Http\Controllers\Website;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Systha\Core\Models\Client;
use Systha\Core\Models\Contact;
use Systha\Core\Models\EmailOtp;
use Systha\Core\Models\EmailTemplate;
use Systha\Core\Models\ServiceCategory;
use Systha\Core\Services\VendorMailService;
use Systha\Core\Services\EmailTemplateService;
use Systha\Core\Http\Controllers\BaseController;


class FormVerificationController extends BaseController
{

    public function loginSignup(Request $request)
    {
        $request->validate([
            "email" => "required"
        ]);

        $email = $request->email;
        $vendor = $this->vendor;

        $otp = rand(100000, 999999);

        $emailOtp = EmailOtp::updateOrCreate(
            ['email' => $request->email],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(5),
            ]
        );



        $templateService = app(EmailTemplateService::class);

        $emailTemplate = EmailTemplate::where('code', 'otp-temp')->first();

        // dd($emailTemplate);

        if (!$emailTemplate) {
            Log::warning('Email template not found: otp-temp');
            return;
        }

        $mailService = app(VendorMailService::class, ["vendor" => $vendor]);


            $data = [
                "otp" => $otp
            ];
            // Render email subject and content
            $rendered = $templateService->load($emailTemplate, $data)->render();


            // Prepare email details
            $emailData = [
                'from_email' => $vendor->contact->email,
                'from_name' => $vendor->name,
                'to_email' => $email,
                'to_name' => '',
                'subject' => $rendered['subject'],
                'message' => $rendered['content'], // HTML allowed
                'cc' => [],      // optional: add if needed
                'bcc' => [],     // optional: add if needed
                'attachments' => [], // optional: add files if needed
                'table_name' => $emailOtp->getTable(),
                'table_id' => $emailOtp->id,
            ];

            // Send email
            $result = $mailService->send($emailData);

            if($result["success"]) {
                Log::info("Email sent successfully to {$email}");
                $temp = view($this->viewPath.'::website.auth.login-otp', compact('email'))->render();
    
                return response([
                    "temp" => $temp,
                ], 200);
            } else {
                return response([
                    'message' => $result['message'] ?? null,
                    'error' => $result['error'] ?? null,
                    'status' => $result['status'],
                ], 500);
            }
    }

  
    public function verifyOTP(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "otp" => "required"
        ]);

        $record = EmailOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        // OTP is valid
        $record->delete();

        $contact = Contact::where('email', $request->email)->first();
        if ($contact) {
            $contact = Auth::guard('contacts')->login($contact); // Logs in user
        } else {
            $client = Client::create([
                "fname" => "Guest",
                "lname" => "Guest",
                "email" => $request->email,
                "password" => bcrypt('password')
            ]);
            $contact = $client->contact()->create([
                "fname" => "Guest",
                "lname" => "Guest",
                "email" => $request->email,
                "password" => bcrypt('password')
            ]);

        }
        $service_categories = ServiceCategory::select('id', 'service_category_name as name', 'description as question_text')->where([
            'is_active' => 1,
            'is_deleted' => 0,
            'vendor_id' => $this->vendor->id
        ])
            ->with(['services' => function ($query) {
                $query->whereNull('parent_id')
                    ->where('is_deleted', 0)
                    ->with('children')
                    ->select('id', 'service_name as name', 'service_category_id', 'question_text', 'price'); // ✅ SELECT only needed fields
            }])
            ->get();

        $stripe_public_key = null;
        if (
            isset($this->vendor) &&
            isset($this->vendor->paymentCredential) &&
            isset($this->vendor->paymentCredential->val1)
        ) {
            $stripe_public_key = $this->vendor->paymentCredential->val1;
        }
        return response()->json(['message' => "Verified", "data" => $contact], 200);
    }
}
PHP;

        $clientLoginTemplate = <<<'PHP'
<?php

namespace __NAMESPACE__\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Systha\Core\Models\Client;
use Systha\Core\Models\Contact;
use Systha\Core\Models\VendorTemplate;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class ClientLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $vendor;
    protected $template;
    protected $menus;
    protected $redirectTo = '/login-dashboard';


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $h = request()->getHttpHost();
        $host = $h;
        if (strpos($h, 'www.') !== false) {
            $indexof = strpos($h, 'www.') + 4;
            $host = substr($h, $indexof, strlen($h) - 1);
        }

        $temp = VendorTemplate::where('template_host', $host)->where('is_active', 1)->where('is_deleted', 0)->first();
        if (!$temp) {
            return redirect('/admin');
        }
        $this->template = $temp;
        $this->vendor = $temp->vendor;
    }

    public function clientLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::guard('webContact')->check()) {
            Auth::guard('webContact')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Attempt login
        $credentials = $request->only('email', 'password');
        $credentials["table_name"] = "clients";
        // dd($credentials);
        if (Auth::guard('webContact')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect("/page/dashboard");
          
        }else{
            dd("no success");
        }

    
        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->withInput($request->only('email'));

    }

    public function login(Request $request)
    {

        if ($this->attemptLogin($request)) {

            return $this->sendLoginResponse($request);
        }
        $this->validateLoginReq($request);

        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {

            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Attempt the login process
     *
     * @return user guard
     */
    public function attemptLogin(Request $request)
    {

        $this->validateLoginReq($request);

        $user = Contact::where(['email' => $request->email, 'is_deleted' => 0])->first();
        if (!$user->where('email', $request->email)->where('is_deleted', 0)->first()) {
            response()->json(['errors' => ['email' => 'Email doesn\'t exist']], 422)->send();
            exit();
        } else {
            $user = $user->where('email', $request->email)->where('is_deleted', 0)->first();
        }

        if (!Hash::check($request->password, $user->password)) {
            response()->json(['errors' => ['password' => 'Password incorrect!']], 422)->send();
            exit();
        }

        return Auth::guard('webContact')->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }

    public function validateLoginReq(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'required' => 'required*'
        ]);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        return $this->authenticated($request, Auth::guard('webContact')->user()->user())
            ?: redirect()->intended($this->redirectPath());
    }

    public function authenticated($request, $user)
    {
        // dd('user',$user);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('webContact')->logout();

        $request->session()->invalidate();
        redirect('/page/login');
    }

}
PHP;

        $baseController = strtr($baseControllerTemplate, [
            '__NAMESPACE__'    => $phpNamespace,
            '__MODULE_LOWER__' => $moduleLower,
            '__MODULE_SLUG__'  => $moduleSlug,
        ]);

        $websiteController = strtr($websiteControllerTemplate, [
            '__NAMESPACE__' => $phpNamespace,
        ]);

        $formVerificationController = strtr($formVerificationTemplate, [
            '__NAMESPACE__' => $phpNamespace,
        ]);

        $clientLoginController = strtr($clientLoginTemplate, [
            '__NAMESPACE__' => $phpNamespace,
        ]);

        $fs->put("{$basePath}/src/Http/Controllers/Website/BaseController.php", $baseController);
        $fs->put("{$basePath}/src/Http/Controllers/Website/WebsiteController.php", $websiteController);
        $fs->put("{$basePath}/src/Http/Controllers/Website/FormVerificationController.php", $formVerificationController);
        $fs->put("{$basePath}/src/Http/Controllers/Auth/ClientLoginController.php", $clientLoginController);

        $routesTemplate = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;
use Systha\Core\Http\Controllers\Form\FormController;
use __NAMESPACE__\Http\Controllers\Website\WebsiteController;
use __NAMESPACE__\Http\Controllers\Auth\ClientLoginController;
use __NAMESPACE__\Http\Controllers\Website\FormVerificationController;


Route::group(['middleware' => ['web']], function () {

    Route::get('/logo/{file_name}', [WebsiteController::class, 'logo'])->name('logo');

    Route::get('/form-service-categories', [WebsiteController::class, 'serviceCategories'])->name('service.categories');

    Route::post('/login-signup', [FormVerificationController::class, 'loginSignup'])->name('login.signup');

    Route::post('/verify-otp', [FormVerificationController::class, 'verifyOTP'])->middleware('web')->name('verify.otp');

    Route::get('/', [WebsiteController::class, 'page'])->name('home');

    Route::get('blogs/{code}', [WebsiteController::class, 'blogDetail'])->name('blog.detail');

    Route::get('forms/{code}', [WebsiteController::class, 'page'])->name('form');

    Route::get('services/{code}', [WebsiteController::class, 'page'])->name('service.detail');

    Route::get('package-subscriptions/{slug}', [WebsiteController::class, 'packageSubscription'])->name('package.subscriptions');

    Route::get('/{code}', [WebsiteController::class, 'page'])->name('page');

    Route::post('/login', [ClientLoginController::class, 'clientLogin'])->name('login');

    Route::group(['middleware' => ['auth:webContact']], function () {
        Route::post('/logout', [ClientLoginController::class, 'logout'])->name('logout');
    });

});
PHP;

        $routes = strtr($routesTemplate, [
            '__NAMESPACE__'     => $phpNamespace,
        ]);

        $fs->put("{$basePath}/routes/web.php", $routes);
        $this->info("Backend (routes + Website controllers) created under: {$basePath}");

        /* -------------------------------------------------------------
         | 7) Blade view structure
         * ------------------------------------------------------------- */
        $masterTemplate = <<<'BLADE'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>__MODULE_STUDLY__ Website</title>

    <script src="{{ asset('websites/__MODULE_SLUG__/assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('websites/__MODULE_SLUG__/assets/js/jquery.inputmask.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('websites/__MODULE_SLUG__/assets/css/fontawesome-6.0.0.min.css') }}">

    @vite(['resources/assets/css/main.css', 'resources/assets/js/main.js'], 'websites/__MODULE_SLUG__/build')
</head>
<body>
    @include($viewPath.'::website.layout.header')

    <main>
        @yield('content')
    </main>

    @include($viewPath.'::website.layout.footer')
</body>
</html>
BLADE;

        $headerTemplate = <<<'BLADE'
<header class="website-header">
    <h1>__MODULE_STUDLY__ Website</h1>
</header>
BLADE;

        $footerTemplate = <<<'BLADE'
<footer class="website-footer">
    <p>&copy; {{ date('Y') }} __MODULE_STUDLY__</p>
</footer>
BLADE;

        $indexTemplate = <<<'BLADE'
@extends($viewPath.'::website.layout.master')
@section('content')
    @foreach ($components as $component)
            @include($viewPath.'::components.' . $component->component_name)
    @endforeach
@endsection
BLADE;

        $viewReplacements = [
            '__MODULE_STUDLY__' => $moduleStudly,
            '__MODULE_SLUG__'   => $moduleSlug,
        ];

        $master = strtr($masterTemplate, $viewReplacements);
        $header = strtr($headerTemplate, $viewReplacements);
        $footer = strtr($footerTemplate, $viewReplacements);
        $index  = $indexTemplate;

        $fs->put("{$basePath}/resources/views/website/layout/master.blade.php", $master);
        $fs->put("{$basePath}/resources/views/website/layout/header.blade.php", $header);
        $fs->put("{$basePath}/resources/views/website/layout/footer.blade.php", $footer);
        $fs->put("{$basePath}/resources/views/website/index.blade.php", $index);
        $this->info("Website blade views created under: {$basePath}/resources/views/website");

        $copies = [
            [
                'source' => __DIR__ . '/temp_folder/components',
                'target' => "{$basePath}/resources/views/components",
                'name' => 'Default components',
            ],
            [
                'source' => __DIR__ . '/temp_folder/auth',
                'target' => "{$basePath}/resources/views/website/auth",
                'name' => 'Default auth views',
            ],
        ];

        foreach ($copies as $copy) {
            if ($fs->isDirectory($copy['source'])) {
                $fs->copyDirectory($copy['source'], $copy['target']);
                $this->info("{$copy['name']} copied from {$copy['source']} to {$copy['target']}.");
            } else {
                $this->warn("{$copy['name']} folder not found at {$copy['source']}. Leaving {$copy['target']} empty.");
            }
        }


        /* -------------------------------------------------------------
         | 8) Default assets (CSS/JS)
         * ------------------------------------------------------------- */
        $assetsBase = "{$basePath}/resources/assets";
        $tempAssetsPath = __DIR__ . '/temp_folder/assets';

        if ($fs->isDirectory($tempAssetsPath)) {
            $fs->copyDirectory($tempAssetsPath, $assetsBase);
            $this->info("Default assets copied from {$tempAssetsPath} to {$assetsBase}.");
        } else {
            $cssTemplate = <<<'CSS'
:root {
    color-scheme: light;
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    background-color: #f8fafc;
    color: #0f172a;
}

.module-wrapper {
    margin: 0 auto;
    max-width: 720px;
    padding: 3rem 1.5rem;
}
CSS;

        $jsTemplate = <<<'JS'
document.addEventListener('DOMContentLoaded', () => {
    console.info('Default module assets loaded. Customize resources/assets/js/main.js for your package.');
});
JS;

            $fs->put("{$assetsBase}/css/main.css", $cssTemplate);
            $fs->put("{$assetsBase}/js/main.js", $jsTemplate);

            $this->info("Default assets created under {$assetsBase}.");
        }

        /* -------------------------------------------------------------
         | 9) Module-local package.json + vite.config.js (Node)
         * ------------------------------------------------------------- */
        $this->info('Creating module-local package.json and vite.config.js ...');

        $nodePackageJson = <<<'JSON'
{
  "private": true,
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  },
  "devDependencies": {
    "vite": "^5.0.0"
  }
}
JSON;

        $fs->put("{$basePath}/package.json", $nodePackageJson);

        $tempViteConfig = __DIR__ . '/temp_folder/vite.config.js';

        if ($fs->isFile($tempViteConfig)) {
            $fs->copy($tempViteConfig, "{$basePath}/vite.config.js");
            $this->info("Vite config copied from {$tempViteConfig}.");
        } else {
            $viteConfigTemplate = <<<'JS'
import { defineConfig } from "vite";
import path from "node:path";

const flattenName = (key) => {
    const normalized = key.replace(/\\/g, "/");

    if (normalized.startsWith("resources/assets/")) {
        return normalized
            .replace("resources/assets/", "")
            .replace(/\//g, "-")
            .replace(/\.[^.]+$/, "");
    }

    return normalized.replace(/\//g, "-").replace(/\.[^.]+$/, "");
};

// all entry files that you want to use with @vite()
const inputs = [
    "resources/assets/js/main.js",
    "resources/assets/css/main.css"
];

// turn the list into an object { 'path': resolvedPath, ... }
const inputEntries = Object.fromEntries(
    inputs.map((key) => [key, path.resolve(__dirname, key)])
);

export default defineConfig({
    root: __dirname,
    publicDir: false,
    build: {
        outDir: "resources/build",
        manifest: true,
        emptyOutDir: true,
        rollupOptions: {
            input: inputEntries,
            output: {
                entryFileNames: (chunk) => {
                    const key = chunk.facadeModuleId
                        ? chunk.facadeModuleId.replace(__dirname + "/", "")
                        : chunk.name;

                    return `assets/${flattenName(key)}.js`;
                },
                assetFileNames: (chunkInfo) => {
                    const ext =
                        chunkInfo.name && chunkInfo.name.includes(".")
                            ? "[extname]"
                            : ".css";

                    return `assets/${flattenName(
                        chunkInfo.name || "asset"
                    )}${ext}`;
                },
            },
        },
    },
    server: {
        port: 5175,
        strictPort: false,
    },
});
JS;

            $fs->put("{$basePath}/vite.config.js", $viteConfigTemplate);
            $this->info("Default Vite config created at: {$basePath}/vite.config.js");
        }

        $this->info("Module-local Vite setup created at: {$basePath}");

        /* -------------------------------------------------------------
         | 10) Run npm install + npm run build
         * ------------------------------------------------------------- */
        $this->info('Running "npm install" inside package module (first time may take a while)...');

        if (! $this->runProcess(['npm', 'install'], $basePath)) {
            $this->error('npm install failed inside package module. You can run it manually:');
            $this->line("  cd " . $this->relativePath($basePath) . " && npm install && npm run build");
            return self::FAILURE;
        }

        $this->info('Running "npm run build" inside package module...');

        if (! $this->runProcess(['npm', 'run', 'build'], $basePath)) {
            $this->error('npm run build failed inside package module. Run it manually:');
            $this->line("  cd " . $this->relativePath($basePath) . " && npm run build");
            return self::FAILURE;
        }

        $packageBuildPath  = "{$basePath}/resources/build";
        $packageAssetsPath = "{$basePath}/resources/assets";
        $publicAssetsPath  = public_path("{$assetPrefix}/{$moduleSlug}");
        $publicBuildPath   = "{$publicAssetsPath}/build";
        $publicManifest    = "{$publicAssetsPath}/manifest.json";
        $publicAssetsCopy  = "{$publicAssetsPath}/assets";

        if ($fs->exists($publicAssetsPath)) {
            $fs->deleteDirectory($publicAssetsPath);
        }

        if (! $fs->exists($packageBuildPath)) {
            $this->warn("Build output not found at {$packageBuildPath}. Skipping publish to public path.");
        } else {
            $fs->copyDirectory($packageBuildPath, $publicBuildPath);

            $manifestSource = "{$publicBuildPath}/.vite/manifest.json";
            if ($fs->exists($manifestSource)) {
                $fs->copy($manifestSource, "{$publicBuildPath}/manifest.json");
                $fs->copy($manifestSource, $publicManifest);
            } else {
                $this->warn("Vite manifest not found at {$manifestSource}. Run npm run build again if @vite fails.");
            }

            $this->info("Assets built at {$packageBuildPath} and published to {$publicBuildPath}.");
        }

        if ($fs->exists($packageAssetsPath)) {
            $fs->copyDirectory($packageAssetsPath, $publicAssetsCopy);
            $this->info("Static assets copied to {$publicAssetsCopy}.");
        } else {
            $this->warn("Assets directory not found at {$packageAssetsPath}. Skipping static copy.");
        }

        /* -------------------------------------------------------------
         | 11) Package PHP composer.json + root composer.json integration
         * ------------------------------------------------------------- */
        $this->info('Creating PHP composer.json for the package ...');

        $fullPackageName = "{$vendorSlug}/{$moduleSlug}";

        // package composer.json
        $composerData = [
            'name'        => $fullPackageName,
            'description' => "Package {$moduleStudly}",
            'type'        => 'library',
            'autoload'    => [
                'psr-4' => [
                    "{$phpNamespace}\\" => 'src/',
                ],
            ],
            'extra'       => [
                'laravel' => [
                    'providers' => [
                        "{$phpNamespace}\\{$moduleStudly}ServiceProvider",
                    ],
                ],
            ],
        ];

        $composerJson = json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $fs->put("{$basePath}/composer.json", $composerJson);

        $this->info("Package composer.json created at: {$basePath}/composer.json");

        // Update ROOT composer.json so composer require/update works
        $rootComposerPath = base_path('composer.json');

        if ($fs->exists($rootComposerPath)) {
            $rootJson = json_decode($fs->get($rootComposerPath), true) ?: [];

            // Ensure repositories[path] entry exists
            $pathRepo = [
                'type'    => 'path',
                'url'     => 'packages/*/*',
                'options' => ['symlink' => true],
            ];

            $repositories = $rootJson['repositories'] ?? [];

            $alreadyHasPathRepo = false;
            foreach ($repositories as $repo) {
                if (
                    isset($repo['type'], $repo['url']) &&
                    $repo['type'] === 'path' &&
                    $repo['url'] === 'packages/*/*'
                ) {
                    $alreadyHasPathRepo = true;
                    break;
                }
            }

            if (! $alreadyHasPathRepo) {
                $repositories[]           = $pathRepo;
                $rootJson['repositories'] = $repositories;
                $this->info('Added path repository "packages/*/*" to root composer.json.');
            }

            // Ensure require entry exists
            if (! isset($rootJson['require'])) {
                $rootJson['require'] = [];
            }

            if (! isset($rootJson['require'][$fullPackageName])) {
                // *@dev allows usage even if minimum-stability is stable
                $rootJson['require'][$fullPackageName] = '*@dev';
                $this->info("Added {$fullPackageName}: \"*@dev\" to root composer.json require section.");
            }

            $fs->put(
                $rootComposerPath,
                json_encode($rootJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );

            $this->info('Root composer.json updated.');
        } else {
            $this->warn('Root composer.json not found; please add path repository and require entry manually.');
        }

        $this->line('');

        /* -------------------------------------------------------------
         | 10) Run composer update + clear cache automatically
         * ------------------------------------------------------------- */
        $this->info("Running composer update for package {$fullPackageName} ...");

        if (! $this->runProcess(['composer', 'update', $fullPackageName], base_path())) {
            $this->error("composer update failed. Run manually: composer update {$fullPackageName}");
        } else {
            $this->info("composer update completed successfully.");
        }

        $this->info('Clearing Laravel route cache...');
        $this->runProcess(['php', 'artisan', 'route:clear'], base_path());

        $this->info('Clearing Laravel optimize cache...');
        $this->runProcess(['php', 'artisan', 'optimize:clear'], base_path());

        $this->info('Laravel caches cleared.');

        $this->info('Running auth:replace-config...');
        $this->runProcess(['php', 'artisan', 'auth:replace-config'], base_path());

        $this->info('✅ Package module created and wired into composer.');

        $this->line('');
        $this->line('Final steps (if something failed above):');
        $this->line("1) composer update {$fullPackageName}");
        $this->line('2) php artisan route:clear');
        $this->line("3) php artisan serve");
        $this->line("4) Open: http://localhost:8000/{$moduleSlug}");
        return self::SUCCESS;
    }

    protected function runProcess(array $command, string $cwd): bool
    {
        $process = new Process($command, $cwd);
        $process->setTimeout(null);

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->isSuccessful();
    }

    protected function relativePath(string $path): string
    {
        return str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
    }

}
