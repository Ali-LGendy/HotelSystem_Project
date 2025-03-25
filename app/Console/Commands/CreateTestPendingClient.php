<?php

namespace App\Console\Commands;

use Database\Seeders\TestPendingClientSeeder;
use Illuminate\Console\Command;

class CreateTestPendingClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-test-pending-client';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test client with pending approval status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating test pending client...');
        
        $seeder = new TestPendingClientSeeder();
        $seeder->run();
        
        $this->info('Test pending client created successfully!');
        $this->info('Email: pending@example.com');
        $this->info('Password: password');
        
        return Command::SUCCESS;
    }
}