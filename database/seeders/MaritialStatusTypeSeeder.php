<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaritialStatusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [

            	'id' => 1,
            	'name' => 'Single'
            ],
            [
                'id' => 2,
                'name' => 'Married'
            ],
            [
                'id' => 3,
                'name' => 'Widowed'
            ],
        	[
        		'id' => 4,
        		'name' => 'Divorced'
        	]
        ];
        DB::table('maritial_status_types')->delete();
        DB::table('maritial_status_types')->insert($data);
    }
}
