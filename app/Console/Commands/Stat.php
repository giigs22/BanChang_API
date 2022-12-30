<?php

namespace App\Console\Commands;

use App\Http\Controllers\StatDataController;
use Illuminate\Console\Command;

class Stat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:stat {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Average By Type Data';

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
        $stat = new StatDataController;
        $type = $this->argument('type');
        $stat->getData($type);
    }
}
