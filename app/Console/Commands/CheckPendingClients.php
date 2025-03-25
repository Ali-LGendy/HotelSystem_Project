<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPendingClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-pending-clients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for pending clients directly in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for pending clients...');
        
        // Direct SQL query to check for pending clients
        $pendingClients = DB::select("SELECT id, name, email, role, is_approved FROM users WHERE role = 'client' AND is_approved = 0");
        
        if (empty($pendingClients)) {
            $this->info('No pending clients found in the database.');
        } else {
            $this->info('Found ' . count($pendingClients) . ' pending clients:');
            
            $headers = ['ID', 'Name', 'Email', 'Role', 'Is Approved'];
            $rows = [];
            
            foreach ($pendingClients as $client) {
                $rows[] = [
                    $client->id,
                    $client->name,
                    $client->email,
                    $client->role,
                    $client->is_approved ? 'Yes' : 'No'
                ];
            }
            
            $this->table($headers, $rows);
        }
        
        // Check if there are any clients at all
        $allClients = DB::select("SELECT COUNT(*) as count FROM users WHERE role = 'client'");
        $this->info('Total clients in database: ' . $allClients[0]->count);
        
        return Command::SUCCESS;
    }
}