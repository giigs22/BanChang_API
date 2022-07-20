<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $add = new Role();
        $add->name = "Administrator";
        $add->save();

        $add2 = new Role();
        $add2->name = "Staff";
        $add2->save();

        $add3 = new Role();
        $add3->name = "General";
        $add3->save();

    }
}
