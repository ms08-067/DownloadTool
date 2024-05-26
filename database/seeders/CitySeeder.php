<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cities = [
            'An Giang',
            'Bà Rịa - Vũng Tàu',
            'Bắc Giang',
            'Bắc Kạn',
            'Bạc Liêu',
            
            'Bắc Ninh',
            'Bến Tre',
            'Bình Định',
            'Bình Dương',
            'Bình Phước',
            'Bình Thuận',
            
            'Cà Mau',
            'Cần Thơ',
            'Cao Bằng',
            'Đà Nẵng',
            'Đắk Lắk',
            'Đắk Nông',
            'Điện Biên',
            
            'Đồng Nai',
            'Đồng Tháp',
            'Gia Lai',
            'Hà Giang',
            'Hà Nam',
            'Hà Nội',
            'Hà Tĩnh',
            
            'Hải Dương',
            'Hải Phòng',
            'Hậu Giang',
            'Hòa Bình',
            'Hưng Yên',
            'Khánh Hòa',
            
            'Kiên Giang',
            'Kon Tum',
            'Lai Châu',
            'Lâm Đồng',
            'Lạng Sơn',
            'Lào Cai',
            'Long An',
            
            'Nam Định',
            'Nghệ An',
            'Ninh Bình',
            'Ninh Thuận',
            'Phú Thọ',
            'Phú Yên',
            'Quảng Bình',
            
            'Quảng Nam',
            'Quảng Ngãi',
            'Quảng Ninh',
            'Quảng Trị',
            'Sóc Trăng',
            'Sơn La',
            
            'Tây Ninh',
            'Thái Bình',
            'Thái Nguyên',
            'Thanh Hóa',
            'Huế',
            'Tiền Giang',
            
            'TP. HCM',
            'Trà Vinh',
            'Tuyên Quang',
            'Vĩnh Long',
            'Vĩnh Phúc',
            'Yên Bái'
        ];

        $data = [];
        $actives = ['Hà Nội'];
        foreach ($cities as $idx => $city){
            $sort = 100;$active=0;$idx++;
            if(in_array($city, $actives)){
                $sort = array_search($city, $actives)+1;
                $active = 1;
            }
            $data[] = [
                'id'         => $idx,
                'name'       => $city,
                'slug'       => Str::slug($city),
                'active'     => "$active",
                'sort'       => $sort,
            ];
        }

        DB::table('cities')->delete();
        DB::table('cities')->insert($data);
    }
}
