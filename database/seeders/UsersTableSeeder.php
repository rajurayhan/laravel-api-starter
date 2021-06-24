<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = [
            'name'          => 'Raju Rayhan', 
            'email'         => 'raju@lhgraphics.com', 
            'password'      => bcrypt('lhg@2020'),
            'phone'         => '8801849699001', 
            'address'       => '20, Nur Graden City', 
            'country_id'    => 15, 
            'state_id'      => null, 
            'city'          => 'Dhaka', 
            'zip'           => '1212'
        ];

        User::create($user);
    }
}
