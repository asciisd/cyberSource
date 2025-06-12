<?php

namespace Asciisd\CyberSource\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cybersource:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the CyberSource package';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Installing CyberSource package...');

        $this->info('Publishing configuration...');
        $this->publishConfiguration();

        $this->info('Publishing migrations...');
        $this->publishMigrations();

        $this->info('Installation complete!');
    }

    /**
     * Publish the package configuration.
     *
     * @return void
     */
    protected function publishConfiguration()
    {
        $this->callSilent('vendor:publish', [
            '--provider' => 'Asciisd\CyberSource\CyberSourceServiceProvider',
            '--tag' => 'cybersource-config',
        ]);

        $this->info('CyberSource configuration published successfully.');
    }

    /**
     * Publish the package migrations.
     *
     * @return void
     */
    protected function publishMigrations()
    {
        $this->callSilent('vendor:publish', [
            '--provider' => 'Asciisd\CyberSource\CyberSourceServiceProvider',
            '--tag' => 'cybersource-migrations',
        ]);

        $this->info('CyberSource migrations published successfully.');
    }
}
