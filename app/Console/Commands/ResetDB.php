<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database reset';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Switch to maintenance mode
        info('maintenance mode');
        Artisan::call('down');

        // Environment variable replacement
        info('env replacement');
        $this->envReplace();

        // Database resetting
        info('db resetting');
        $this->call('migrate:fresh');

        // Database seeding
        info('db seeding');
        $this->call('db:seed');

        // Switch to live mode
        info('back to normal mode');
        Artisan::call('up');
    }

    private function envReplace()
    {
        if (env('APP_DEFAULT_LANGUAGE') != 'en') {
            envReplace('APP_DEFAULT_LANGUAGE', 'en');
        }

        if (env('APP_TIMEZONE') != 'Asia/Dhaka') {
            envReplace('APP_TIMEZONE', 'Asia/Dhaka');
        }

        if (env('APP_CURRENCY') != 'USD') {
            envReplace('APP_CURRENCY', 'USD');
        }

        if (env('APP_CURRENCY_SYMBOL') != '$') {
            envReplace('APP_CURRENCY_SYMBOL', '$');
        }

        if (env('APP_CURRENCY_SYMBOL_POSITION') != 'left') {
            envReplace('APP_CURRENCY_SYMBOL_POSITION', 'left');
        }
    }
}
