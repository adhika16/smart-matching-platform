<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Job;
use App\Policies\ApplicationPolicy;
use App\Policies\JobPolicy;
use App\Services\Bedrock\BedrockService;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BedrockService::class, function ($app): BedrockService {
            $config = config('bedrock');

            $client = null;

            if (($config['enabled'] ?? false) === true) {
                $client = new BedrockRuntimeClient([
                    'region' => $config['region'] ?? env('AWS_DEFAULT_REGION', 'us-east-1'),
                    'version' => 'latest',
                    'http' => [
                        'timeout' => $config['timeout'] ?? 15,
                    ],
                ]);
            }

            return new BedrockService(
                $config,
                $client,
                $app->make(LoggerInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Job::class, JobPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
    }
}
