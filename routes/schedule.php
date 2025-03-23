<?php

use App\Console\Commands\SendLoginReminderEmails;
use Illuminate\Console\Scheduling\Schedule;

return function (Schedule $schedule) {
    $schedule->command(SendLoginReminderEmails::class)->daily();
};
