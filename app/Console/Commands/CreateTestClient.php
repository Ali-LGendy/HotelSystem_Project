<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateTestClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-test-client';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test client directly in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating test client...');
        
        // Create a test client directly with SQL
        $result = DB::insert("
            INSERT INTO users (
                name, 
                email, 
                password, 
                national_id, 
                mobile, 
                country, 
                gender, 
                role, 
                is_approved,
                created_at,
                updated_at
            ) VALUES (
                'Test Client', 
                'test.client@example.com', 
                ?, 
                '12345678901234', 
                '1234567890', 
                'Test Country', 
                'male', 
                'client', 
                0,
                NOW(),
                NOW()
            )
        ", [Hash::make('password')]);
        
        if ($result) {
            $this->info('Test client created successfully!');
            $this->info('Email: test.client@example.com');
            $this->info('Password: password');
            
            // Verify the client exists with the correct status
            $client = DB::select("SELECT * FROM users WHERE email = 'test.client@example.com'");
            if (!empty($client)) {
                $this->info('Client ID: ' . $client[0]->id);
                $this->info('Is Approved: ' . ($client[0]->is_approved ? 'Yes' : 'No'));
            }
        } else {
            $this->error('Failed to create test client.');
        }
        
        return Command::SUCCESS;
    }
}