<?php

namespace App\Console\Commands;

use Database\Seeders\SimpleClientApprovalSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupSimpleClientApprovalTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-simple-client-approval {--fresh : Whether to refresh the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up simplified test data for client approval functionality (users only)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up simplified client approval test data...');
        
        try {
            if ($this->option('fresh')) {
                $this->info('Refreshing database...');
                Artisan::call('migrate:fresh', ['--force' => true]);
                $this->info(Artisan::output());
            }
            
            $this->info('Running SimpleClientApprovalSeeder...');
            $seeder = new SimpleClientApprovalSeeder();
            $seeder->setCommand($this);
            $seeder->run();
            
            $this->info('Test data setup complete!');
        } catch (\Exception $e) {
            $this->error('Error setting up test data: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return Command::FAILURE;
        }
        
        $this->info('');
        $this->info('You can now test the client approval functionality with the following accounts:');
        $this->info('');
        $this->info('Admin:');
        $this->info('Email: admin@example.com');
        $this->info('Password: password');
        $this->info('');
        $this->info('Receptionists:');
        $this->info('Email: receptionist1@example.com');
        $this->info('Email: receptionist2@example.com');
        $this->info('Email: receptionist3@example.com');
        $this->info('Password: password');
        $this->info('');
        $this->info('Pending Clients:');
        $this->info('Email: pending.client1@example.com');
        $this->info('Email: pending.client2@example.com');
        $this->info('Email: pending.client3@example.com');
        $this->info('Password: password');
        $this->info('');
        $this->info('Approved Clients:');
        $this->info('Email: approved.client1@example.com');
        $this->info('Email: approved.client2@example.com');
        $this->info('Email: approved.client3@example.com');
        $this->info('Password: password');
        
        return Command::SUCCESS;
    }
}