<?php 

namespace App\Libraries;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;

class WebApiResponse{
    /**
     * WebAPI Error Response
     * 
     * @param int $status_code HTTP Status Code. 
     * @param array $error_details  Array of Errors. 
     * @param string $message Response Message. 
     * 
     * @return Response 
     *  
     * 
    */

    public static function error(int $status_code, array $error_details = [], string $message)
    {
        $errorResponse = [
            'status'    => 'Error', 
            'message'   => $message, 
            'code'      => $status_code, 
            'errors'    => $error_details
        ];

        return response()->json($errorResponse, $status_code, ['Content-Type' => 'text/json'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * WebAPI Success Response
     * 
     * @param int $status_code HTTP Status Code. 
     * @param array $items  Array of Items. 
     * @param string $message Response Message. 
     * 
     * @return Response 
     *  
     * 
    */

    public static function success(int $status_code = 200, $items = [], string $message = '')
    {
        $successResponse = [
            'status'    => 'Success', 
            'message'   => $message, 
            'code'      => $status_code, 
            'data'      => $items
        ];

        return response()->json($successResponse, $status_code, ['Content-Type' => 'text/json'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * WebAPI Success Response
     * 
     * @param Validator $validator Validator Instance
     * @param Request $request Array of Request Data
     * @param string $message Response Message. 
     * 
     * @return Response 
     *  
     * 
    */

    public static function validationError(Validator $validator, Request $request)
    {
        $items  = []; 
        $errors = $validator->errors()->toArray(); 

        foreach($errors as $index => $error){
            $items[] = [
                'field'     => $index, 
                'value'     => $request[$index], 
                'message'   => $errors[$index]
            ];
        }

        return self::error(
            400, 
            $items, 
            'Validation Error'
        );
    }
}