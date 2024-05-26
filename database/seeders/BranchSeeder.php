<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
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
            'fk_bank_id' => 1,
            'name' => 'CN Gia Lâm',
            'name_eng' => 'Gia Lam Branch',
            'address' => 'Số 741, Đường Nguyễn Đức Thuận',
            'address_eng' => 'No 741, Nguyen Duc Thuan Str',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Bắc Hà',
            'name_eng' => 'Bac Ha Branch',
            'address' => '147 Hoàng Quốc Việt',
            'address_eng' => '147 Hoang Quoc Viet Street',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Tây Hồ',
            'name_eng' => 'Tay Ho Branch',
            'address' => '47 Phan Đình Phùng, P. Quán Thánh, Q. Ba Đình, TP Hà Nội',
            'address_eng' => 'No 47, Phan Dinh Phung Street, Quan Thanh Ward, Ba Dinh District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Từ Liêm',
            'name_eng' => 'Tu Liem Branch',
            'address' => 'Tầng 1 và 2, Tòa nhà CT1 Bắc Hà - C14, phố Tố Hữu',
            'address_eng' => 'Floor 1 and 2, CT1 Tower, Bac Ha C14, To Huu Street, Trung Van Ward, South Tu Liem District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Sơn Tây',
            'name_eng' => 'Son Tay Branch',
            'address' => 'Số 191 Đường Lê Lợi, P. Lê Lợi, TX Sơn Tây, TP Hà Nội',
            'address_eng' => 'No 191, Le Loi Street, Le Loi Ward, Son Tay Town, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Đông Đô',
            'name_eng' => 'Dong Do Branch',
            'address' => '27 Trần Duy Hưng',
            'address_eng' => 'No 27, Tran Duy Hung Avenue, Trung Hoa Ward, Cau Giay District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Thăng Long',
            'name_eng' => 'Thang Long Branch',
            'address' => 'Số 3 Đường Phạm Hùng, P. Mỹ Đình II, Q.Nam Từ Liêm, TP Hà Nội',
            'address_eng' => 'No 3, Pham Hung Road, My Dinh II Ward, Nam Tu Liem District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Hồng Hà',
            'name_eng' => 'Hong Ha Branch',
            'address' => '2A Đại Cồ Việt, P. Lê Đại Hành, Q. Hai Bà Trưng, TP Hà Nội',
            'address_eng' => 'No 2A, Dai Co Viet Street, Le Dai Hanh Ward, Hai Ba Trung District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Hai Bà Trưng',
            'name_eng' => 'Hai Ba Trung Branch',
            'address' => 'Toà nhà VCCI Tower số 9 Đào Duy Anh',
            'address_eng' => 'VCCI Building, No 9, Dao Duy Anh Street',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Hoàn Kiếm',
            'name_eng' => 'Hoan Kiem Branch',
            'address' => 'Số 194 Trần Quang Khải',
            'address_eng' => 'No 194, Tran Quang Khai Street, Ly Thai To Ward, Hoan Kiem District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Ba Đình',
            'name_eng' => 'Ba Dinh Branch',
            'address' => '57 Láng Hạ',
            'address_eng' => 'No 57, Lang Ha Road, Thanh Cong Ward, Ba Dinh District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Hà Thành',
            'name_eng' => 'Ha Thanh Branch',
            'address' => 'Số 74 Thợ Nhuộm',
            'address_eng' => 'No 74, Tho Nhuom Street, Tran Hung Dao Ward, Hoan Kiem District, Ha Noi CityA',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Đông Hà Nội',
            'name_eng' => 'East Ha Noi Branch',
            'address' => 'Số 46 Đường Cao Lỗ Tổ 2',
            'address_eng' => 'No 46, Cao Lỗ Street, Group 2',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Hà Tây',
            'name_eng' => 'Ha Tay Branch',
            'address' => '197 Quang Trung',
            'address_eng' => 'No 197, Quang Trung Street, Quang Trung Ward, Ha Dong District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Thành Đô',
            'name_eng' => 'Thanh Do Branch',
            'address' => 'Số 469 Nguyễn Văn Linh, P. Phúc Đồng, Q. Long Biên, TP Hà Nội',
            'address_eng' => 'No 469, Nguyen Van Linh Street, Phuc Dong Ward, Long Bien District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Sở Giao dịch 1',
            'name_eng' => 'So Giao dich 1 Branch',
            'address' => 'Tháp Vincom, 191 Bà Triệu',
            'address_eng' => 'Vincom Tower, No 191, Ba Trieu Street',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Quang Trung',
            'name_eng' => 'Quang Trung Branch',
            'address' => 'Tòa nhà Prime Center, 53 Quang Trung, P. Nguyễn Du, Q. Hai Bà Trưng, TP Hà Nội',
            'address_eng' => 'Prime Center Building, No 53, Quang Trung Street, Nguyen Du Ward, Hai Ba Trung District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Nam Hà Nội',
            'name_eng' => 'South Ha Noi Branch',
            'address' => 'Số 1281 Đường Giải Phóng, P. Hoàng Liệt, Q. Hoàng Mai, TP Hà Nội',
            'address_eng' => 'No 1281, Giai Phong Street, Hoang Liet Ward, Hoang Mai District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Thanh Xuân',
            'name_eng' => 'Thanh Xuan Branch',
            'address' => 'Center Building, Hapulico Complex, 01 Nguyễn Huy Tưởng, P. Thanh Xuân Trung, Q. Thanh Xuân, TP Hà Nội',
            'address_eng' => 'Center Building, Hapulico Complex, No 01, Nguyen Huy Tuong Street, Thanh Xuan Trung Ward, Thanh Xuan District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Chương Dương',
            'name_eng' => 'Chuong Duong Branch',
            'address' => '41 Hai Bà Trưng',
            'address_eng' => 'No 41, Hai Ba Trung Street, Tran Hung Dao Ward, Hoan Kiem District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Đại La',
            'name_eng' => 'Dai La Branch',
            'address' => 'Số 1B Yết Kiêu',
            'address_eng' => 'No 1B, Yet Kieu Street, Tran Hung Dao Ward, Hoan Kiem District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Thái Hà',
            'name_eng' => 'Thai Ha Branch',
            'address' => 'Tòa nhà Việt, Số 1 phố Thái Hà, Phường Trung Liệt, Quận Đống Đa, Thành phố Hà Nội',
            'address_eng' => 'Viet Building, No. 1 Thai Ha Street, Trung Liet Ward, Dong Da District, Hanoi',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Hà Nội',
            'name_eng' => 'Ha Noi Branch',
            'address' => 'Số 4B Lê Thánh Tông',
            'address_eng' => 'No 4B, Le Thanh Tong Street, Phan Chu Trinh Ward, Hoan Kiem District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Ngọc Khánh Hà Nội',
            'name_eng' => 'Ngoc Khanh Ha Noi Branch',
            'address' => 'Tòa nhà UDIC- 27 Huỳnh Thúc Kháng',
            'address_eng' => 'UDIC Building- No 27 Huynh Thuc Khang Str',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Sở Giao dịch 3',
            'name_eng' => 'So Giao dich 3 Branch',
            'address' => '20 Hàng Tre, P. Lý Thái Tổ, Q. Hoàn Kiếm, TP Hà Nội',
            'address_eng' => 'No 20, Hang Tre Street, Ly Thai To Ward, Hoan Kiem District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Cầu Giấy',
            'name_eng' => 'Cau Giay Branch',
            'address' => 'Số 263 đường Cầu Giấy,',
            'address_eng' => 'No 263, Cau Giay Street, Dich Vong Ward, Cau Giay District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Mỹ Đình',
            'name_eng' => 'My Dinh Branch',
            'address' => 'Tầng 1,2,3 Khu tổ hợp văn phòng, Trung tâm thương mại và chung cư cao cấp Golden Palace , Phường Mễ Trì, Quận Nam Từ Liêm TP Hà Nội, Việt Nam',
            'address_eng' => 'Floor 1, 2, 3 Golden Palace tower , Me Tri Street, Nam Tu Liem District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Quang Minh',
            'name_eng' => 'Quang Minh Branch',
            'address' => 'Km số 9, Đường Bắc Thăng Long - Nội Bài',
            'address_eng' => 'Km 9, North Thang Long - Noi Bai Street, Quang Minh Town, Me Linh District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Đống Đa',
            'name_eng' => 'Dong Da Branch',
            'address' => '71 Nguyễn Chí Thanh',
            'address_eng' => 'No 71, Nguyen Chi Thanh Street, Lang Ha Ward, Dong Da District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Tràng An',
            'name_eng' => 'Trang An Branch',
            'address' => '11 Cửa Bắc, P. Trúc Bạch, Q. Ba Đình, TP Hà Nội',
            'address_eng' => 'No 11, Cua Bac Street, Truc Bach Ward, Ba Dinh District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Bắc Hà Nội',
            'name_eng' => 'North Ha Noi Branch',
            'address' => 'Số 137A Nguyễn Văn Cừ',
            'address_eng' => 'No 137A, Nguyen Van Cu Road, Ngoc Lam Ward, Long Bien District, Ha Noi City',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 1,
            'name' => 'CN Hoàng Mai Hà Nội',
            'name_eng' => 'Hoang Mai Ha Noi Branch',
            'address' => 'Tầng 1 và tầng 2, Tòa nhà CT4 Eco Green City, Khu đô thị Tây Nam Kim Giang I',
            'address_eng' => '1st and 2nd Floor, CT4 Eco Green City Tower, Tay Nam Kim Giang I Urban area',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Đền Lừ',
            'name_eng' => 'Đền Lừ Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Tương Mai',
            'name_eng' => 'Tương Mai Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Kim Đồng',
            'name_eng' => 'Kim Đồng Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Định Công',
            'name_eng' => 'Định Công Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Linh Đàm',
            'name_eng' => 'Linh Đàm Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Ngọc Lâm',
            'name_eng' => 'Ngọc Lâm Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Đức Giang',
            'name_eng' => 'Đức Giang Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Đông Anh',
            'name_eng' => 'Đông Anh Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Nội Bài',
            'name_eng' => 'Nội Bài Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Hàm Nghi ',
            'name_eng' => 'Hàm Nghi  Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Trung Văn ',
            'name_eng' => 'Trung Văn  Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Mỹ Đình ',
            'name_eng' => 'Mỹ Đình  Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Thanh Trì ',
            'name_eng' => 'Thanh Trì  Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Hoàng Hoa',
            'name_eng' => 'Hoàng Hoa Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Cửa Bắc',
            'name_eng' => 'Cửa Bắc Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Liễu Giai',
            'name_eng' => 'Liễu Giai Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Thành Công',
            'name_eng' => 'Thành Công Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Giang Văn Minh',
            'name_eng' => 'Giang Văn Minh Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Ngọc Hà',
            'name_eng' => 'Ngọc Hà Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Hoàng Quốc Việt',
            'name_eng' => 'Hoàng Quốc Việt Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Trần Quốc Hoàn',
            'name_eng' => 'Trần Quốc Hoàn Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Xuân Thủy',
            'name_eng' => 'Xuân Thủy Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Trần Duy Hưng',
            'name_eng' => 'Trần Duy Hưng Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Hồ Tùng Mậu',
            'name_eng' => 'Hồ Tùng Mậu Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Nguyễn Khánh Toàn',
            'name_eng' => 'Nguyễn Khánh Toàn Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'CN Hà Thành Tầng',
            'name_eng' => 'Hà Thành Tầng Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Hoàng Cầu',
            'name_eng' => 'Hoàng Cầu Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Ô Chợ Dừa',
            'name_eng' => 'Ô Chợ Dừa Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Tôn Đức',
            'name_eng' => 'Tôn Đức Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Huỳnh Thúc',
            'name_eng' => 'Huỳnh Thúc Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Khâm Thiên',
            'name_eng' => 'Khâm Thiên Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Giảng Võ',
            'name_eng' => 'Giảng Võ Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Láng Thượng',
            'name_eng' => 'Láng Thượng Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Kim Liên',
            'name_eng' => 'Kim Liên Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Thanh Nhàn',
            'name_eng' => 'Thanh Nhàn Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Lò Đúc',
            'name_eng' => 'Lò Đúc Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'CN Hà Nội',
            'name_eng' => 'Hà Nội Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Bạch Mai',
            'name_eng' => 'Bạch Mai Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Minh Khai',
            'name_eng' => 'Minh Khai Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Trần Đại Nghĩa',
            'name_eng' => 'Trần Đại Nghĩa Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Đồng Xuân',
            'name_eng' => 'Đồng Xuân Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Bát Đàn',
            'name_eng' => 'Bát Đàn Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'CN Thăng Long',
            'name_eng' => 'Thăng Long Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Tràng Thi',
            'name_eng' => 'Tràng Thi Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Trần Quốc Toản',
            'name_eng' => 'Trần Quốc Toản Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Nguyễn Hữu Huân',
            'name_eng' => 'Nguyễn Hữu Huân Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Cửa Nam',
            'name_eng' => 'Cửa Nam Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Âu Cơ',
            'name_eng' => 'Âu Cơ Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Tây Hồ',
            'name_eng' => 'Tây Hồ Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Nguyễn Thị Định',
            'name_eng' => 'Nguyễn Thị Định Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Thanh Xuân',
            'name_eng' => 'Thanh Xuân Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'CN Đông Đô',
            'name_eng' => 'Đông Đô Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Hà Đông',
            'name_eng' => 'Hà Đông Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
        [
            'fk_bank_id' => 2,
            'name' => 'PGD Văn Quán',
            'name_eng' => 'Văn Quán Branch',
            'address' => '',
            'address_eng' => '',
            'coordinates' => ''
        ],
    ];
    DB::table('bank_branches')->delete();
    DB::table('bank_branches')->insert($data);
}
}
