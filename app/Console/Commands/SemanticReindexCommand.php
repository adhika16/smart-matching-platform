<?php

namespace App\Console\Commands;

use App\Jobs\SyncCreativeProfileEmbeddings;
use App\Jobs\SyncJobEmbeddings;
use App\Models\CreativeProfile;
use App\Models\Job;
use Illuminate\Console\Command;

class SemanticReindexCommand extends Command
{
    protected $signature = 'semantic:rebuild
        {entity=all : Target entity to rebuild (jobs, profiles, all)}
        {--chunk=100 : Number of records processed per chunk}
        {--queue : Dispatch jobs to the queue instead of running synchronously}';

    protected $description = 'Rebuild semantic embeddings for jobs and creative profiles.';

    public function handle(): int
    {
        $entity = strtolower((string) $this->argument('entity'));
        $chunk = (int) $this->option('chunk');
        $queue = (bool) $this->option('queue');

        if ($chunk <= 0) {
            $chunk = 100;
        }

        $targets = match ($entity) {
            'jobs' => ['jobs'],
            'profiles', 'creatives' => ['profiles'],
            'all' => ['jobs', 'profiles'],
            default => null,
        };

        if ($targets === null) {
            $this->error('Invalid entity. Accepted values: jobs, profiles, all.');

            return self::INVALID;
        }

        if (in_array('jobs', $targets, true)) {
            $this->rebuildJobs($chunk, $queue);
        }

        if (in_array('profiles', $targets, true)) {
            $this->rebuildProfiles($chunk, $queue);
        }

        $this->info('Semantic embeddings rebuild complete.');

        return self::SUCCESS;
    }

    private function rebuildJobs(int $chunk, bool $queue): void
    {
        $this->info('Rebuilding job embeddings...');

        Job::query()
            ->select('id')
            ->orderBy('id')
            ->chunk($chunk, function ($jobs) use ($queue): void {
                foreach ($jobs as $job) {
                    if ($queue) {
                        SyncJobEmbeddings::dispatch($job->id, true);
                    } else {
                        SyncJobEmbeddings::dispatchSync($job->id, true);
                    }
                }
            });
    }

    private function rebuildProfiles(int $chunk, bool $queue): void
    {
        $this->info('Rebuilding creative profile embeddings...');

        CreativeProfile::query()
            ->select('id')
            ->orderBy('id')
            ->chunk($chunk, function ($profiles) use ($queue): void {
                foreach ($profiles as $profile) {
                    if ($queue) {
                        SyncCreativeProfileEmbeddings::dispatch($profile->id, true);
                    } else {
                        SyncCreativeProfileEmbeddings::dispatchSync($profile->id, true);
                    }
                }
            });
    }
}
