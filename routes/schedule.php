<?php

use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\SendLoginReminderEmails;

return function (Schedule $schedule) {
    $schedule->command(SendLoginReminderEmails::class)->daily();
};