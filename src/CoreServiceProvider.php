<?php

/**
 * THIS INTELLECTUAL PROPERTY IS COPYRIGHT Ⓒ 2020
 * SYSTHA TECH LLC. ALL RIGHT RESERVED
 * -----------------------------------------------------------
 * SALES@SYSTHATECH.COM
 * 512 903 2202
 * WWW.SYSTHATECH.COM
 * -----------------------------------------------------------
 */

namespace Systha\Core;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Systha\Core\Commands\InstallCommand;
use Systha\Core\Interfaces\MailServiceInterface;
use Systha\Core\Lib\Services\MailTransporter;
use Systha\Core\Mail\SendOnlineMail;
use Systha\Core\Middleware\Cleaners\CleanCardNumber;
use Systha\Core\Middleware\Cleaners\CleanCvvNumber;
use Systha\Core\Middleware\Cleaners\CleanPhoneNumber;
use Systha\Core\Middleware\Cleaners\CleanZipNumber;
use Systha\Core\Middleware\EnsureAppCode;
use Systha\Core\Middleware\RefreshPlatformToken;
use Systha\Core\Middleware\VerifyVendorClientDomain;
use Systha\Core\Models\Vendor;
use Systha\Core\Services\CustomMailService;
use Systha\Core\Services\DefaultMailService;
use Systha\Core\Services\EmailTemplateService;
use Systha\Core\Services\MailService;
use Systha\Core\Services\QuotationService;
use Systha\Core\Services\StripeService;
use Systha\Core\Services\VendorMailService;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EmailTemplateService::class, fn () => new EmailTemplateService());
        $this->app->singleton(QuotationService::class, fn () => new QuotationService());
        $this->app->singleton(DefaultMailService::class, fn () => new DefaultMailService());
        $this->app->singleton(CustomMailService::class, fn () => new CustomMailService());

        $this->app->bind(VendorMailService::class, function ($app, array $params) {
            if (! isset($params['vendor']) || ! $params['vendor'] instanceof Vendor) {
                throw new \InvalidArgumentException('Vendor instance must be passed when resolving VendorMailService');
            }

            return new VendorMailService($params['vendor']);
        });

        $this->app->bind(StripeService::class, function ($app, array $params) {
            if (! isset($params['vendor']) || ! $params['vendor'] instanceof Vendor) {
                throw new \InvalidArgumentException('Vendor instance is required.');
            }

            return new StripeService($params['vendor']);
        });
    }

    public function boot(): void
    {
        $this->mapRelations()
            ->loadSandboxRoutes()
            ->loadSandboxMigrations()
            ->loadSandBoxCommands();

        $this->loadViews();
        $this->sendOnlineMail();
        $this->loadCustomServices();
        $this->loadCustomMiddleware();
    }

    protected function mapRelations(): self
    {
        Relation::morphMap([]);

        return $this;
    }

    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'core');
    }

    protected function loadSandboxRoutes(): self
    {
        foreach ($this->getRoutes() as $routePath) {
            $this->loadRoutesFrom($routePath);
        }

        return $this;
    }

    public function getRoutes(): array
    {
        $routesPath = __DIR__ . '/routes';

        if (! File::isDirectory($routesPath)) {
            return [];
        }

        return array_map(
            fn ($route) => $route->getPathName(),
            File::allFiles($routesPath)
        );
    }

    protected function loadSandboxMigrations(): self
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');

        return $this;
    }

    public function loadSandBoxCommands(): self
    {
        $commands = $this->discoverCommandClasses();
        if (! in_array(InstallCommand::class, $commands, true)) {
            $commands[] = InstallCommand::class;
        }

        $this->commands($commands);

        return $this;
    }

    protected function discoverCommandClasses(): array
    {
        $commandsPath = __DIR__ . '/Commands';
        $commandFiles = File::isDirectory($commandsPath) ? File::allFiles($commandsPath) : [];
        $commands = [];

        foreach ($commandFiles as $file) {
            $relative = $file->getRelativePathname();
            $class = 'Systha\\Core\\Commands\\' . str_replace(['/', '\\', '.php'], ['\\', '\\', ''], $relative);

            if (class_exists($class)) {
                $commands[] = $class;
            }
        }

        return $commands;
    }

    public function sendOnlineMail(): void
    {
        $this->app->bind('sendOnlineMail.mailer', function ($app, $parameters) {
            if ($this->app->environment('development')) {
                return Mail::html($parameters['content_message'], function ($mail) use ($parameters) {
                    $mail->to($parameters['to'])->subject($parameters['subject']);
                });
            }

            return new MailTransporter(
                $parameters,
                new SendOnlineMail(
                    $parameters['subject'],
                    $parameters['content_message'],
                    $parameters['id'],
                    $parameters
                )
            );
        });
    }

    public function loadCustomServices(): void
    {
        $this->app->bind(MailServiceInterface::class, MailService::class);
    }

    public function loadCustomMiddleware(): void
    {
        $router = $this->app['router'];

        $router->middlewareGroup('cleanMasked', [
            CleanPhoneNumber::class,
            CleanZipNumber::class,
            CleanCardNumber::class,
            CleanCvvNumber::class,
        ]);

        foreach ($this->discoverMiddlewareClasses() as $middlewareClass) {
            $alias = $this->middlewareAliasMap()[$middlewareClass]
                ?? 'core.' . Str::snake(class_basename($middlewareClass), '.');

            $router->aliasMiddleware($alias, $middlewareClass);
        }
    }

    protected function discoverMiddlewareClasses(): array
    {
        $middlewarePath = __DIR__ . '/Middleware';
        $middlewareFiles = File::isDirectory($middlewarePath) ? File::allFiles($middlewarePath) : [];
        $middlewares = [];

        foreach ($middlewareFiles as $file) {
            $relative = $file->getRelativePathname();
            $class = 'Systha\\Core\\Middleware\\' . str_replace(['/', '\\', '.php'], ['\\', '\\', ''], $relative);

            if (class_exists($class)) {
                $middlewares[] = $class;
            }
        }

        return $middlewares;
    }

    protected function middlewareAliasMap(): array
    {
        return [
            EnsureAppCode::class => 'platform.appcode',
            RefreshPlatformToken::class => 'platform.token.refresh',
            VerifyVendorClientDomain::class => 'vendor.client.domain',
        ];
    }
}
