<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Job;
use App\Policies\ApplicationPolicy;
use App\Policies\JobPolicy;
use App\Services\Bedrock\BedrockService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Scout;
use Meilisearch\Client as MeilisearchClient;
use Probots\Pinecone\Client as PineconeClient;
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
                $client = App::make('aws')->createClient('BedrockRuntime');
            }

            return new BedrockService(
                $config,
                $client,
                $app->make(LoggerInterface::class)
            );
        });

        if (! $this->app->bound(MeilisearchClient::class)) {
            $this->app->singleton(MeilisearchClient::class, function ($app): MeilisearchClient {
                $config = $app['config']->get('scout.meilisearch', []);

                $host = $config['host'] ?? null;

                if (empty($host)) {
                    throw new \RuntimeException('Meilisearch host is not configured. Set MEILISEARCH_HOST in your environment.');
                }

                return new MeilisearchClient(
                    $host,
                    $config['key'] ?? null,
                    clientAgents: [sprintf('Meilisearch Laravel Scout (v%s)', Scout::VERSION)]
                );
            });
        }

        $this->app->singleton(PineconeClient::class, function (): PineconeClient {
            return new PineconeClient(
                config('pinecone.api_key'),
                config('pinecone.index_host'),
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
