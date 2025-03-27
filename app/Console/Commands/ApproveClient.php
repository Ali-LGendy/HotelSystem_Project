<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ApproveClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:approve {client_id} {receptionist_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Approve a client by setting is_approved to 1 and assigning a receptionist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clientId = $this->argument('client_id');
        $receptionistId = $this->argument('receptionist_id');

        $client = User::findOrFail($clientId);
        $receptionist = User::findOrFail($receptionistId);

        if (!$receptionist->hasRole('receptionist')) {
            $this->error('The specified user is not a receptionist.');
            return 1;
        }

        if (!$client->hasRole('client')) {
            $this->error('The specified user is not a client.');
            return 1;
        }

        $client->is_approved = 1;
        $client->manager_id = $receptionistId;
        $client->save();

        $this->info("Client {$client->name} (ID: {$client->id}) has been approved by receptionist {$receptionist->name} (ID: {$receptionist->id})");
        return 0;
    }
}