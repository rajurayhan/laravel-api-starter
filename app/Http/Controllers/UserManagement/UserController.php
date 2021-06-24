<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\User as UserResource;
use App\Http\Resources\Users\UserCollection;
use App\Libraries\WebApiResponse;
use App\Models\Reports;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    /**
     * User List.
     * @group User Management
     * @authenticated
     *
     * @param Request $request
     * @queryParam limit integer optional Data Per Page Limit. Example : 10
     *
     * @return \Illuminate\Http\Response
     * @return Response
     * @response 200 {"status":"success","message":"User List","code":200,"data":[{"id":1,"name":"Raju Rayhan","email":"raju@lhgraphics.com","phone":"8801849699001","address":"20, Nur Graden City","country":"Bangladesh","country_id":15,"state":null,"state_id":null,"zip":"1212","created_at":"2021-01-26T17:30:31.000000Z","updated_at":"2021-01-26T17:30:31.000000Z"},{"id":2,"name":"Nicolas Heathcote","email":"kgusikowski@example.net","phone":"3","address":"20, Nur Graden City","country":"Bangladesh","country_id":15,"state":null,"state_id":null,"zip":"1212","created_at":"2021-01-26T17:30:31.000000Z","updated_at":"2021-01-26T17:30:31.000000Z"},{"id":3,"name":"Madelynn Morissette Sr.","email":"enos.koss@example.com","phone":"11","address":"20, Nur Graden City","country":"Bangladesh","country_id":15,"state":null,"state_id":null,"zip":"1212","created_at":"2021-01-26T17:30:31.000000Z","updated_at":"2021-01-26T17:30:31.000000Z"},{"id":4,"name":"Sean Rogahn","email":"nils.yundt@example.com","phone":"7","address":"20, Nur Graden City","country":"Bangladesh","country_id":15,"state":null,"state_id":null,"zip":"1212","created_at":"2021-01-26T17:30:31.000000Z","updated_at":"2021-01-26T17:30:31.000000Z"},{"id":5,"name":"Prof. Tyra Borer","email":"huel.cornell@example.com","phone":"2","address":"20, Nur Graden City","country":"Bangladesh","country_id":15,"state":null,"state_id":null,"zip":"1212","created_at":"2021-01-26T17:30:31.000000Z","updated_at":"2021-01-26T17:30:31.000000Z"}],"links":{"first":"http:\/\/localhost:8000\/api\/users?page=1","last":"http:\/\/localhost:8000\/api\/users?page=3","prev":null,"next":"http:\/\/localhost:8000\/api\/users?page=2"},"meta":{"current_page":1,"from":1,"last_page":3,"links":[{"url":null,"label":"&laquo; Previous","active":false},{"url":"http:\/\/localhost:8000\/api\/users?page=1","label":1,"active":true},{"url":"http:\/\/localhost:8000\/api\/users?page=2","label":2,"active":false},{"url":"http:\/\/localhost:8000\/api\/users?page=3","label":3,"active":false},{"url":"http:\/\/localhost:8000\/api\/users?page=2","label":"Next &raquo;","active":false}],"path":"http:\/\/localhost:8000\/api\/users","per_page":"5","to":5,"total":12}}
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 10;
        try {
            $users = User::paginate($limit);
            return new UserCollection($users);
        } catch (\Throwable $th) {
             $errors = $th->getMessage();
             return WebApiResponse::error(500, [$errors], 'Something Went Wrong');
        }
    }

    /**
     * Create New User
     * @group User Management
     * @authenticated
     * @param  \Illuminate\Http\Request  $request
     * @bodyParam name string required User Name. Example: Raju Rayhan
     * @bodyParam email string required User Email. Example: raju@lhgraphics.com
     * @bodyParam phone string required User Email. Example: 01849699001
     * @bodyParam password string required User Password. Example: 12345678
     * @bodyParam password_confirmation string required User Password Confirmation. Example: 12345678
     * @bodyParam address string required User Address. Example: 20, Nur Graden City
     * @bodyParam country_id integer required User Country ID. Example: 15
     * @bodyParam state_id integer optional User State ID. Example: null
     * @bodyParam city string optional User City. Example: Dhaka
     * @bodyParam zip integer required User Postal code. Example: 1230
     * @bodyParam report string/file optional User Report. Example: report.pdf
     * @bodyParam report_expired_at date required if report exists. Example: 2021-01-20
     * @return \Illuminate\Http\Response
     * @response 200 {"status":"Success","message":"User Created","code":201,"data":{"id":12,"name":"Raju Rayhan","email":"raju@aalhgraphics.com","phone":"01849699001","address":"20, Nur Graden City","country":"Bangladesh","country_id":15,"state":null,"state_id":0,"zip":1230,"created_at":"2021-01-26T17:47:52.000000Z","updated_at":"2021-01-26T17:47:52.000000Z"}}
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'phone'         => 'required|string',
            'password'      => 'required|confirmed|min:8,max:14',
            'address'       => 'required|string|max:255',
            'country_id'    => 'required|integer',
            'state_id'      => 'required|integer',
            'city'          => 'required|string',
            'zip'           => 'required|integer',
            'report'                    => 'nullable|max:10000|mimes:doc,docx,pdf',
            'report_expired_at'         => 'required_with:report|date'
        ]);

        if($validator->fails()){
            return WebApiResponse::validationError($validator, $request);
        }
        $userObj = new User();

        $userObj->name         = $request->name;
        $userObj->email        = $request->email;
        $userObj->phone        = $request->phone;
        $userObj->password     = bcrypt($request->password);
        $userObj->address      = $request->address;
        $userObj->country_id   = $request->country_id;
        $userObj->state_id     = $request->state_id;
        $userObj->city         = $request->city;
        $userObj->zip          = $request->zip;

        $user = $userObj->save();

        if($user){
            if($request->hasFile('report')){
                $uploadFolder = 'reports';
                $report = $request->file('report');
                $fileName = uniqid().'_'.str_replace(' ', '_', $report->getClientOriginalName());
                $report_uploaded_path = $report->storeAs($uploadFolder, $fileName, 'public');

                $reportObj = new Reports();

                $reportObj->name = $report->getClientOriginalName();
                $reportObj->user_id         = $userObj->id;
                $reportObj->generated_at    = date('Y-m-d');
                $reportObj->expired_at      = $request->report_expired_at;
                $reportObj->file_path       = $report_uploaded_path;

            }
        }

        $userData = new UserResource($userObj);
        return WebApiResponse::success(201, $userData, 'User Created');

    }

    /**
     * Display the specified user.
     * @group User Management
     * @authenticated
     * @param  Request $request
     * @urlParam user int required User ID. Example : 5
     * @return \Illuminate\Http\Response
     * @response 200 {"status":"Success","message":"User Data","code":200,"data":{"id":12,"name":"Raju Rayhan","email":"raju@aalhgraphics.com","phone":"01849699001","address":"20, Nur Graden City","country":"Bangladesh","country_id":15,"state":null,"state_id":0,"zip":"1230","created_at":"2021-01-26T17:47:52.000000Z","updated_at":"2021-01-26T17:47:52.000000Z"}}
     */
    public function show(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $userData = new UserResource($user);
            return WebApiResponse::success(200, $userData, 'User Data');
        } catch (\Throwable $th) {
            $errors = $th->getMessage();
            return WebApiResponse::error(500, [$errors], 'Something Went Wrong');
        }
    }

    /**
     * Update User
     * @group User Management
     * @authenticated
     * @param  \Illuminate\Http\Request  $request
     * @urlParam user int required User id. Example: 5
     * @bodyParam name string required User Name. Example: Raju Rayhan
     * @bodyParam email string required User Email. Example: raju@lhgraphics.com
     * @bodyParam phone string required User Email. Example: 01849699001
     * @bodyParam password string optional User Password. Example: 12345678
     * @bodyParam password_confirmation string optional User Password Confirmation. Example: 12345678
     * @bodyParam address string required User Address. Example: 20, Nur Graden City
     * @bodyParam country_id integer required User Country ID. Example: 15
     * @bodyParam state_id integer optional User State ID. Example: null
     * @bodyParam city string optional User City. Example: Dhaka
     * @bodyParam zip integer required User Postal code. Example: 1230
     * @bodyParam status boolean required User Status. Example: 1
     * @return \Illuminate\Http\Response
     * @response 200 {"status":"Success","message":"User Updated","code":201,"data":{"id":12,"name":"Raju Rayhan","email":"raju@aalhgraphics.com","phone":"01849699001","address":"20, Nur Graden City","country":"Bangladesh","country_id":15,"state":null,"state_id":0,"zip":120,"created_at":"2021-01-26T17:47:52.000000Z","updated_at":"2021-01-26T17:49:57.000000Z"}}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,'.$id,
            'phone'         => 'required|string',
            'password'      => 'nullable|confirmed|min:8,max:14',
            'address'       => 'required|string|max:255',
            'country_id'    => 'required|integer',
            'state_id'      => 'required|integer',
            'city'          => 'required|string',
            'zip'           => 'required|integer',
            'status'        => 'required|boolean',
            // 'report'                    => 'nullable|max:10000|mimes:doc,docx,pdf',
            // 'report_expired_at'         => 'required_with:report|date'
        ]);

        if($validator->fails()){
            return WebApiResponse::validationError($validator, $request);
        }
        $userObj = new User();

        $user   = $userObj->findOrFail($id);

        $user->name         = $request->name;
        $user->email        = $request->email;
        $user->phone        = $request->phone;

        $user->address      = $request->address;
        $user->country_id   = $request->country_id;
        $user->state_id     = $request->state_id;
        $user->city         = $request->city;
        $user->zip          = $request->zip;

        if($request->filled('password')){
            $user->password     = bcrypt($request->password);
        }

        $user->save();

        $userData = new UserResource($user);
        return WebApiResponse::success(201, $userData, 'User Updated');


    }

    /**
     * Remove the specified User.
     * @group User Management
     * @authenticated
     *
     * @urlParam user int required User ID. Example : 5
     * @return \Illuminate\Http\Response
     *
     * @response 200 {"status":"Success","message":"User Deleted","code":200,"data":[]}
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        try {
            $user->delete();
            return WebApiResponse::success(200, [], 'User Deleted');
        } catch (\Throwable $th) {
            $errors = $th->getMessage();
            return WebApiResponse::error(500, [$errors], 'Something Went Wrong');
        }
    }
}
