<?php

namespace Modules\DoubleEntry\Console;

use Illuminate\Console\Command;

class DoubleEntrySeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'double-entry:seed {company}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed double entry data for new company';
    
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
        $class = $this->laravel->make(\Modules\DoubleEntry\Database\Seeders\DoubleEntrySeeder::class);

        $seeder = $class->setContainer($this->laravel)->setCommand($this);

        $seeder->__invoke();
    }
}
