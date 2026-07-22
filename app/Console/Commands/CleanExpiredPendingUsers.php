<?php

namespace App\Console\Commands;

use App\Models\PendingUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanExpiredPendingUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pending-users:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired pending user registrations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up expired pending user registrations...');

        // Delete expired pending users
        $deletedCount = PendingUser::where('expires_at', '<', now())->count();

        // Also clean up related password_resets records
        $expiredEmails = PendingUser::where('expires_at', '<', now())->pluck('email');

        // Delete expired pending users
        PendingUser::where('expires_at', '<', now())->delete();

        // Clean up related password_resets
        if ($expiredEmails->isNotEmpty()) {
            DB::table('password_resets')->whereIn('email', $expiredEmails)->delete();
        }

        $this->info("Cleaned up {$deletedCount} expired pending user registration(s).");

        return Command::SUCCESS;
    }
}
