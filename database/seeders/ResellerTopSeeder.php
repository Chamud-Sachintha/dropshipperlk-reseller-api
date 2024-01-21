<?php

namespace Database\Seeders;

use App\Helpers\AppHelper;
use App\Models\Reseller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResellerTopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sellerInfo = array();
        $sellerInfo['fullName'] = 'Chamud Sachintha';
        $sellerInfo['bName'] = 'chamud123';
        $sellerInfo['address'] = 'Kiribathgoda';
        $sellerInfo['phoneNumber'] = '12345678';
        $sellerInfo['nicNumber'] = '200034304198';
        $sellerInfo['profit_total'] = 0.0;
        $sellerInfo['email'] = 'chamudsachintha999@gmail.com';
        $sellerInfo['password'] = Hash::make(123);
        $sellerInfo['refCode'] = Str::random(5);
        $sellerInfo['code'] = Str::random(5);
        $sellerInfo['createTime'] = (new AppHelper())->get_date_and_time();

        (new Reseller())->add_log($sellerInfo);
    }
}
