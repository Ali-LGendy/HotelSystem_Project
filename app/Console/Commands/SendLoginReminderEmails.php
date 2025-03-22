<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\LoginReminderNotification;
use Carbon\Carbon;

class SendLoginReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send-login-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to clients who haven’t logged in for the past month.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::where('last_login_at', '<', Carbon::now()->subMonth())
                    ->orWhereNull('last_login_at') // Include users who never logged in // extra
                    ->get();

        foreach ($users as $user) {
            $user->notify(new LoginReminderNotification($user));
        }

        $this->info('Login reminder emails sent successfully!');
    }
}
