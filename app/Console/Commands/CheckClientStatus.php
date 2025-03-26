<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckClientStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-client-status {email? : The email of the client to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the status of clients in the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if ($email) {
            // Check a specific client
            $client = User::where('email', $email)->first();
            
            if (!$client) {
                $this->error("No client found with email: {$email}");
                return Command::FAILURE;
            }
            
            $this->displayClientInfo($client);
        } else {
            // Show all clients
            $this->info('Pending Clients:');
            $pendingClients = User::where('role', 'client')
                ->where('is_approved', 0)
                ->get();
                
            if ($pendingClients->isEmpty()) {
                $this->info('No pending clients found.');
            } else {
                $this->displayClientsTable($pendingClients);
            }
            
            $this->info('');
            $this->info('Approved Clients:');
            $approvedClients = User::where('role', 'client')
                ->where('is_approved', 1)
                ->get();
                
            if ($approvedClients->isEmpty()) {
                $this->info('No approved clients found.');
            } else {
                $this->displayClientsTable($approvedClients);
            }
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Display detailed information about a client
     */
    private function displayClientInfo($client)
    {
        $this->info("Client Information for: {$client->name}");
        $this->info("Email: {$client->email}");
        $this->info("ID: {$client->id}");
        $this->info("National ID: {$client->national_id}");
        $this->info("Mobile: {$client->mobile}");
        $this->info("Country: {$client->country}");
        $this->info("Gender: {$client->gender}");
        $this->info("Approval Status: " . ($client->is_approved ? 'Approved' : 'Pending'));
        $this->info("Ban Status: " . ($client->is_banned ? 'Banned' : 'Not Banned'));
        
        if ($client->manager_id) {
            $manager = User::find($client->manager_id);
            $this->info("Manager: {$manager->name} (ID: {$manager->id})");
        } else {
            $this->info("Manager: None");
        }
        
        // Show reservations
        $reservations = $client->reservations;
        if ($reservations->isEmpty()) {
            $this->info("Reservations: None");
        } else {
            $this->info("Reservations:");
            $headers = ['ID', 'Room', 'Check-in', 'Check-out', 'Status', 'Price'];
            $rows = [];
            
            foreach ($reservations as $reservation) {
                $rows[] = [
                    $reservation->id,
                    $reservation->room->room_number,
                    $reservation->check_in_date,
                    $reservation->check_out_date,
                    $reservation->status,
                    '$' . number_format($reservation->price_paid / 100, 2),
                ];
            }
            
            $this->table($headers, $rows);
        }
    }
    
    /**
     * Display a table of clients
     */
    private function displayClientsTable($clients)
    {
        $headers = ['ID', 'Name', 'Email', 'National ID', 'Mobile', 'Country', 'Approved', 'Manager'];
        $rows = [];
        
        foreach ($clients as $client) {
            $manager = $client->manager_id ? User::find($client->manager_id)->name : 'None';
            
            $rows[] = [
                $client->id,
                $client->name,
                $client->email,
                $client->national_id,
                $client->mobile,
                $client->country,
                $client->is_approved ? 'Yes' : 'No',
                $manager,
            ];
        }
        
        $this->table($headers, $rows);
    }
}