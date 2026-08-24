<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReceiverAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [

            [
                'name'         => 'Vodafone Cash',
                'key'          => 'vodafone_cash',
                'logo'         => 'receiver-account-logos/vodafone-cash.png',
                'country_code' => 'EG',
                'country_name' => 'Egypt',
                'is_active'    => true,
            ],
            [
                'name'         => 'InstaPay',
                'key'          => 'instapay',
                'logo'         => 'receiver-account-logos/instapay.png',
                'country_code' => 'EG',
                'country_name' => 'Egypt',
                'is_active'    => true,
            ],
            [
                'name'         => 'Orange Cash',
                'key'          => 'orange_cash',
                'logo'         => 'receiver-account-logos/orange-cash.png',
                'country_code' => 'EG',
                'country_name' => 'Egypt',
                'is_active'    => true,
            ],
            [
                'name'         => 'Etisalat Cash',
                'key'          => 'etisalat_cash',
                'logo'         => '/receiver-account-logos/etisalat-cash.png',
                'country_code' => 'EG',
                'country_name' => 'Egypt',
                'is_active'    => true,
            ],
            [
                'name'         => 'Fawry',
                'key'          => 'fawry',
                'logo'         => '/receiver-account-logos/fawry.png',
                'country_code' => 'EG',
                'country_name' => 'Egypt',
                'is_active'    => true,
            ],

            // ===== السعودية =====
            [
                'name'         => 'STC Pay',
                'key'          => 'stc_pay',
                'logo'         => '/receiver-account-logos/stc-pay.png',
                'country_code' => 'SA',
                'country_name' => 'Saudi Arabia',
                'is_active'    => true,
            ],
            [
                'name'         => 'Urpay',
                'key'          => 'urpay',
                'logo'         => '/receiver-account-logos/urpay.png',
                'country_code' => 'SA',
                'country_name' => 'Saudi Arabia',
                'is_active'    => true,
            ],
            [
                'name'         => 'Apple Pay',
                'key'          => 'apple_pay_sa',
                'logo'         => '/receiver-account-logos/apple-pay.png',
                'country_code' => 'SA',
                'country_name' => 'Saudi Arabia',
                'is_active'    => true,
            ],

            // ===== الإمارات =====
            [
                'name'         => 'Apple Pay',
                'key'          => 'apple_pay_ae',
                'logo'         => '/receiver-account-logos/apple-pay.png',
                'country_code' => 'AE',
                'country_name' => 'UAE',
                'is_active'    => true,
            ],
            [
                'name'         => 'Etisalat Wallet',
                'key'          => 'etisalat_wallet_ae',
                'logo'         => '/receiver-account-logos/etisalat-wallet.png',
                'country_code' => 'AE',
                'country_name' => 'UAE',
                'is_active'    => true,
            ],

            // ===== الكويت =====
            [
                'name'         => 'Zain Cash',
                'key'          => 'zain_cash_kw',
                'logo'         => '/receiver-account-logos/zain-cash.png',
                'country_code' => 'KW',
                'country_name' => 'Kuwait',
                'is_active'    => true,
            ],
            [
                'name'         => 'Benefit Pay',
                'key'          => 'benefit_pay',
                'logo'         => '/receiver-account-logos/benefit-pay.png',
                'country_code' => 'KW',
                'country_name' => 'Kuwait',
                'is_active'    => true,
            ],

            // ===== الأردن =====
            [
                'name'         => 'Zain Cash',
                'key'          => 'zain_cash_jo',
                'logo'         => '/receiver-account-logos/zain-cash.png',
                'country_code' => 'JO',
                'country_name' => 'Jordan',
                'is_active'    => true,
            ],
            [
                'name'         => 'eFAWATEERcom',
                'key'          => 'efawateer',
                'logo'         => '/receiver-account-logos/efawateer.png',
                'country_code' => 'JO',
                'country_name' => 'Jordan',
                'is_active'    => true,
            ],

            // ===== عالمي =====
            [
                'name'         => 'PayPal',
                'key'          => 'paypal',
                'logo'         => '/receiver-account-logos/paypal.png',
                'country_code' => 'GLOBAL',
                'country_name' => 'Global',
                'is_active'    => true,
            ],
            [
                'name'         => 'Wise',
                'key'          => 'wise',
                'logo'         => '/receiver-account-logos/wise.png',
                'country_code' => 'GLOBAL',
                'country_name' => 'Global',
                'is_active'    => true,
            ],
        ];

        DB::table('receiver_accounts')->insert($accounts);
    }
}
