<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct(){
        // echo json_encode(Auth::user());exit;
    }

    /**
     * @param $result
     * @param $message
     * @return mixed
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }

    /**
     * @param $error
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $errorMessages = json_decode($errorMessages);
            if($error=='REQUIRED_FIELDS_MISSING'){
                foreach ($errorMessages as $key => $value) {
                    if(isset($value[0])){
                        $response['message'] = $value[0];
                    }
                    break;
                }
            }
            $response['data'] = $errorMessages;
        }
        
        return response()->json($response, $code);
    }
}
