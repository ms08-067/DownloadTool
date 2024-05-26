<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfficeSeeder extends Seeder
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
                'name' => 'NCH Office',
                'slug' => Str::slug('NCH Office'),
                'address' => ''
            ],
            [
                'name' => 'DC Office',
                'slug' => Str::slug('DC Office'),
                'address' => ''
            ],
        ];

        DB::table('offices')->delete();
        DB::table('offices')->insert($data);
    }
}
