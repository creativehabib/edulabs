<?php

namespace Modules\LMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\LMS\Models\Certificate\Certificate;
use Modules\LMS\Models\PaymentMethod;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $paymentMethods = [
            [
                'secret_key' =>  '',
                'publishable_key' =>  '',
                'method_name' => 'stripe',
                'payment_mode' =>  0,
                'logo' =>  'lms-0kPLJgOe.png',
                'status' => 1,

            ],

            [
                'secret_key' =>  '',
                'publishable_key' =>  '',
                'method_name' => 'paypal',
                'payment_mode' =>  0,
                'logo' =>  'lms-OHJRvpAh.png',
                'status' => 1,

            ],
        ];

        foreach ($paymentMethods as $paymentMethod) {
            PaymentMethod::updateOrCreate(['method_name' => $paymentMethod['method_name']], $paymentMethod);
        }
    }
}
