<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SimpleClientApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds to test client approval functionality.
     * This is a simplified version that only creates users without reservations.
     */
    public function run(): void
    {
        // Make sure roles and permissions are set up
        $this->call(RolesAndPermissionsSeeder::class);

        // Create admin user
        $admin = $this->createAdmin();
        
        // Create receptionist users
        $receptionists = $this->createReceptionists();
        
        // Create pending clients (not approved)
        $pendingClients = $this->createPendingClients();
        
        // Create approved clients with managers
        $approvedClients = $this->createApprovedClients($receptionists);
        
        // Output summary
        $this->command->info('Simple Client Approval Seeder completed successfully!');
        $this->command->info('Created:');
        $this->command->info('- 1 Admin user');
        $this->command->info('- ' . count($receptionists) . ' Receptionist users');
        $this->command->info('- ' . count($pendingClients) . ' Pending clients');
        $this->command->info('- ' . count($approvedClients) . ' Approved clients');
    }
    
    /**
     * Create admin user
     */
    private function createAdmin()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'national_id' => '1000000000001',
            'mobile' => '1000000001',
            'country' => 'Admin Country',
            'gender' => 'male',
            'role' => 'admin',
            'is_approved' => 1,
        ]);
        
        $admin->assignRole('admin');
        
        return $admin;
    }
    
    /**
     * Create receptionist users
     */
    private function createReceptionists($count = 3)
    {
        $receptionists = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $receptionist = User::create([
                'name' => "Receptionist User {$i}",
                'email' => "receptionist{$i}@example.com",
                'password' => Hash::make('password'),
                'national_id' => "3000000000{$i}",
                'mobile' => "3000000{$i}",
                'country' => 'Receptionist Country',
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'role' => 'receptionist',
                'is_approved' => 1,
            ]);
            
            $receptionist->assignRole('receptionist');
            $receptionists[] = $receptionist;
        }
        
        return $receptionists;
    }
    
    /**
     * Create pending clients (not approved)
     */
    private function createPendingClients($count = 5)
    {
        $pendingClients = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $client = User::create([
                'name' => "Pending Client {$i}",
                'email' => "pending.client{$i}@example.com",
                'password' => Hash::make('password'),
                'national_id' => "4000000000{$i}",
                'mobile' => "4000000{$i}",
                'country' => 'Client Country',
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'role' => 'client',
                'is_approved' => 0, // Not approved
                'manager_id' => null,
            ]);
            
            $client->assignRole('client');
            $pendingClients[] = $client;
        }
        
        return $pendingClients;
    }
    
    /**
     * Create approved clients with managers
     */
    private function createApprovedClients($receptionists, $count = 5)
    {
        $approvedClients = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $client = User::create([
                'name' => "Approved Client {$i}",
                'email' => "approved.client{$i}@example.com",
                'password' => Hash::make('password'),
                'national_id' => "5000000000{$i}",
                'mobile' => "5000000{$i}",
                'country' => 'Client Country',
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'role' => 'client',
                'is_approved' => 1, // Approved
                'manager_id' => $receptionists[($i - 1) % count($receptionists)]->id,
            ]);
            
            $client->assignRole('client');
            $approvedClients[] = $client;
        }
        
        return $approvedClients;
    }
}