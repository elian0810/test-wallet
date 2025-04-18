<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customers = [
            [
                'document' => '1001941903',
                'name'     => 'Cocacola',
                'email'    => 'Cocacola@yopmal.om',
                'phone'    => '3110251024',
                'credit_line' => [
                    'balance'           => 100000,
                    'total_debt'        => 0,
                    'total_consumption' => 100000,
                ],
            ],
            [
                'document' => '1001941803',
                'name'     => 'Postobom',
                'email'    => 'Postobom@yopmal.om',
                'phone'    => '3010251024',
                'credit_line' => [
                    'balance'           => 250000,
                    'total_debt'        => 0,
                    'total_consumption' => 0,
                ],
            ],
        ];
    
        foreach ($customers as $data) {
            $customer = Customer::create([
                'document' => $data['document'],
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'],
            ]);
    
            $customer->creditLine()->create($data['credit_line']);
        }
    }
    
}
