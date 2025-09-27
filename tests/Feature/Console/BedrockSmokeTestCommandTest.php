<?php

use Illuminate\Console\Command;
use function Pest\Laravel\artisan;

it('notifies developers when Bedrock is disabled', function () {
    config(['bedrock.enabled' => false]);

    artisan('bedrock:smoke-test')
        ->expectsOutputToContain('Bedrock integration is disabled')
        ->assertExitCode(Command::FAILURE);
});
