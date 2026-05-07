<?php


namespace QueryLens\QueryLens\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'querylens:install';
    protected $description = 'Install QueryLens and publish config';

    public function handle()
    {
        $this->info('Installing QueryLens...');

        $this->call('vendor:publish', [
            '--tag' => 'querylens-config'
        ]);

        $this->info('QueryLens installed successfully. Visit /querylens to start debugging!');
    }
}
