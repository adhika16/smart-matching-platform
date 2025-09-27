<?php

namespace App\Console\Commands;

use App\Services\Bedrock\BedrockService;
use App\Services\Bedrock\Exceptions\BedrockException;
use Illuminate\Console\Command;

class BedrockSmokeTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bedrock:smoke-test {--text=Hello from Bedrock! : Text used to test embedding generation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a simple smoke test against the AWS Bedrock integration.';

    public function __construct(private readonly BedrockService $bedrock)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->bedrock->isEnabled()) {
            $this->warn('Bedrock integration is disabled. Update your environment configuration to enable it.');

            return self::FAILURE;
        }

        $text = (string) $this->option('text');

        try {
            $embedding = $this->bedrock->generateEmbeddings($text);
            $this->info(sprintf('✅ Embedding generated successfully (dimension: %d).', count($embedding)));
        } catch (BedrockException $exception) {
            $this->error('❌ Embedding generation failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        try {
            $sampleDescription = $this->bedrock->generateProjectDescription([
                'title' => 'Freelance brand identity refresh',
                'deliverables' => 'Logo suite, typography system, color palette',
                'timeline' => '4 weeks',
            ]);

            $this->info('✅ Content generation succeeded. Sample output:');
            $this->line($sampleDescription === '' ? '[Empty response]' : $sampleDescription);
        } catch (BedrockException $exception) {
            $this->error('⚠️ Content generation failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
