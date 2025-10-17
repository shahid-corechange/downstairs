<?php

namespace App\Providers;

use App\Contracts\NotificationService;
use App\Contracts\SMSService;
use App\Contracts\StorageService;
use App\Models\PersonalAccessToken;
use App\Services\Azure\BlobStorage\AzureBlobStorageService;
use App\Services\Azure\NotificationHub\AzureNotificationHubService;
use App\Services\SMS\Elks46SMSService;
use Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        SMSService::class => Elks46SMSService::class,
        StorageService::class => AzureBlobStorageService::class,
        NotificationService::class => AzureNotificationHubService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment(['staging', 'production'])) {
            URL::forceScheme('https');
        }

        if ($this->app->environment('local')) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(function () {
            $rule = Password::min(8)->letters()->mixedCase()->symbols()->numbers();

            return $this->app->environment('production') ? $rule->uncompromised() : $rule;
        });

        Auth::provider('cachedEloquent', function ($app, array $config) {
            return new CachedEloquentUserProvider($app['hash'], $config['model']);
        });

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
