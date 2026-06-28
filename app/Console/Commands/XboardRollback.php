<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class XboardRollback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xboard:rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rolling back XBoard';

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
     * @return mixed
     */
    public function handle()
    {
        $this->info('Please wait while the database is being rolled back...');
            \Artisan::call("migrate:rollback");
            $this->info(\Artisan::output());
    }
}
