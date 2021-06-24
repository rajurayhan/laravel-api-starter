<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\User as UserResource;
use App\Libraries\WebApiResponse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use Validator;
class AuthController extends Controller
{
    /**
     * Login User and Create Token
     * @group Authentication
     * @param  Request $request
     * @bodyParam email string required User Email. Example: raju@lhgraphics.com
     * @bodyParam password string required User Password. Example: 12345678
     * @return Response
     * @response 200 {"status":"success","message":"Login Success","code":200,"data":{"access_token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiOTBhZDIzM2EwYzNjZjhjNTI0NzVkMDU0NTNhZjQ5NWQxNzJmMjBkN2NhMmVlZTExMjcxZTdiOWVkZTMzYTMzMWMwNWNjY2RlNzVkZDgxNWIiLCJpYXQiOjE2MDY3NjM5MjYsIm5iZiI6MTYwNjc2MzkyNiwiZXhwIjoxNjM4Mjk5OTI2LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.WpwcqKxa6b4rrcdP784ISk6iY81k6qPACfqneZDJ2SStHHoKPH59GrXy-CNPgxea4mFuTPkOJXZAcS408A2kgDVZj5V8Li3Da0SN2Iyr2pXUa-M-NGdROTjYKaRFVY_NtAnc9MKKHVxdvUZgSsZHNB1E3NJVrfdyJa7Jntd3-QlkfdamszCyAGhduRPVTcc9nqknc5P-Ak2Yn6UbADXuvqEhvN3BJ_HKEIyjf_pkvjiKQG0aDYyBWOPxmyt60Hko89E2qg4ekVI7URYZ32sV0k-DiVQmamtZGcyy93kxDVJiZU4-iTwjGOO2brZBtnq8oR4SFLdlGtQipx9x0U0BgP_Il4cFBa6VlGWp4hK5PPpNTelrcONRBK2swJtEBa5IH5JsjSzvZTmjgyJ3kYo12dXpaMnJrc995D88MJF1OPJs29T2b5FXKhlAlFOfB0CsTbt5f5kQPjavHUFTH_H1_2Jh47JvRBwIQX0-sGE4J4Qz3h0Iv-DA8_GZZeCmdHcejGYWVx5eW_Mh-qh1yGrb3BziiuLG2GW5wMOqxjxqLG4YyD6CfmHFG6ugF4RRFw3iXhvglwtLqapwSsgdyr5uVWe2LWR5AFd9PpJine980e7hPfg7ilLZwuxuf_tjBLl3UR5PthrKZ5LrDl8KxBTs3vC0VBsQbIOTkSSv9zGP4uI","token_type":"Bearer","expires_at":"2021-11-30 11:18:46"}}
     */
    public function login(Request $request){
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        if($validator->fails()){
            return WebApiResponse::validationError($validator, $request);
        }
        $user = User::where('email', $request->email)->get();

        if(!$user){
            $errors = [
                [
                    'field'     => '',
                    'value'     => '',
                    'message'   => ['User Not Found']
                ]
            ];

            return WebApiResponse::error(400, $errors, 'User Not Found');
        }

        if(!Auth::attempt($request->all())){
            $errors = [
                [
                    'field'     => '',
                    'value'     => '',
                    'message'   => ['Wrong Credentials']
                ]
            ];

            return WebApiResponse::error(400, $errors, 'Login Failed');
        }

        $personalAccessToken = $this->getAsanaAccessToken();
        $tokenData = [
            'access_token'  => $personalAccessToken->accessToken,
            'token_type'    => 'Bearer',
            'expires_at'    => Carbon::parse($personalAccessToken->token->expires_at)->toDateTimeString()
        ];

        return WebApiResponse::success(200, $tokenData, 'Login Success');
    }

    /**
     * Logout User and Revoke Token
     * @authenticated
     * @group Authentication
     * @return \Illuminate\Http\Response
     * @response 200  {"status":"success","message":"Logout Success","code":200,"data":[]}
     */

    public function logOut(){
        Auth::user()->token()->revoke();
        return WebApiResponse::success(200, [], 'Logout Success');
    }

    /**
     * Get Authenticated User Data
     * @group Authentication
     * @authenticated
     * @param  \Illuminate\Http\Request  $request
     * @response 200  {"status":"Success","message":"User Data","code":200,"data":{"id":1,"name":"Raju Rayhan","email":"raju@lhgraphics.com","phone":"8801849699001","address":"20, Nur Graden City","country":"Bangladesh","country_id":15,"state":null,"state_id":null,"zip":"1212","created_at":"2021-01-26T17:30:31.000000Z","updated_at":"2021-01-26T17:30:31.000000Z"}}
     */

    public function user(Request $request){
        $user = $request->user();
        $userData = new UserResource($user);
        return WebApiResponse::success(200, $userData, 'User Data');

    }

    private function getAsanaAccessToken(){
        if(request()->remember_me === 'true'){
            Passport::personalAccessTokensExpireIn(now()->addDays(15));
        }

        return Auth::user()->createToken('Personal Access Token');
    }
}
