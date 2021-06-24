<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\States;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('countries')->truncate();

        // $path = base_path().'/countries.json';
        // $countries =  json_decode(file_get_contents($path), true);

        // Country::insert($countries);

        $path = base_path().'/countries.json';
        $countries =  json_decode(file_get_contents($path), true);

        $countries = $countries['countries'];

        foreach($countries as $country){
            $countryCreate = Country::create(['name' => $country['name']]);
            if(isset($country['states'])){
                $statesArray = [];
                foreach($country['states'] as $state){
                    $statesArray[] = [
                        'name'          => $state, 
                        'country_id'    => $countryCreate->id
                    ];
                }

                States::insert($statesArray);
            }
        }
    }
}
