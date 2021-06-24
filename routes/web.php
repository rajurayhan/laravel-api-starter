<?php

use App\Models\Country;
use App\Models\States;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
    // $path = base_path().'/countries.json';
    // $countries =  json_decode(file_get_contents($path), true);

    // $countries = $countries['countries'];

    // foreach($countries as $country){
    //     $countryCreate = Country::create(['name' => $country['name']]);
    //     if(isset($country['states'])){
    //         $statesArray = [];
    //         foreach($country['states'] as $state){
    //             $statesArray[] = [
    //                 'name'          => $state, 
    //                 'country_id'    => $countryCreate->id
    //             ];
    //         }

    //         States::insert($statesArray);
    //     }
    // }

    // return Country::with('states')->get()->toJson();
})->name('home');
