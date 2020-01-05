<?php

namespace Modules\DoubleEntry\Database\Seeders;

use Illuminate\Database\Seeder;

class DoubleEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // All Company create accounts
        $this->call(\Modules\DoubleEntry\Database\Seeders\Accounts::class);

        // Just once create classes and types
        $this->call(\Modules\DoubleEntry\Database\Seeders\Classes::class);
        $this->call(\Modules\DoubleEntry\Database\Seeders\Types::class);
    }
}
