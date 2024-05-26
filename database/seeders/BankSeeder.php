<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
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
    			'name' => 'Ngân hàng Đầu tư và Phát triển Việt Nam',
    			'name_eng' => 'Joint Stock Commercial Bank for Investment and Development of Vietnam',
    			'code' => 'BIDV'
    		],
    		[
    			'id' => 2,
    			'name' => 'Ngân hàng Á Châu',
    			'name_eng' => 'Asia Commercial Bank',
    			'code' => 'ACB'
    		],
    		[
    			'id' => 3,
    			'name' => 'Ngân hàng Quân đội',
    			'name_eng' => 'Military Commercial Joint Stock Bank',
    			'code' => 'MBB'
    		],
    		[
    			'id' => 4,
    			'name' => 'Ngoại thương Việt Nam',
    			'name_eng' => 'Vietcombank',
    			'code' => 'VCB'
    		],
    		[
    			'id' => 5,
    			'name' => 'Ngân hàng Hồng Kông và Thượng Hải',
    			'name_eng' => 'The Hongkong and Shanghai Banking Corporation',
    			'code' => 'HSBC'
    		],
    	];
    	DB::table('banks')->delete();
    	DB::table('banks')->insert($data);
    }
}




