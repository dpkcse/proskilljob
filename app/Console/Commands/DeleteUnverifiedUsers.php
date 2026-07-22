<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteUnverifiedUsers extends Command
{
    protected $signature = 'delete:unverified-users';

    protected $description = 'Delete unverified users after a certain period';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Retrieve all users with email_verified_at set to null
        $unverifiedUsers = User::whereNull('email_verified_at')->get();

        // Loop through each unverified user and delete them
        foreach ($unverifiedUsers as $user) {
            $user->delete();
        }
    }
}
