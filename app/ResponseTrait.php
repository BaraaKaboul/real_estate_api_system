<?php

namespace App;

trait ResponseTrait
{
    public function success($message, $code, $data){
        return response()->json([
            'success'=>$message,
            'code'=>$code,
            'data'=>$data
        ]);
    }

    public function fail($message, $code){
        return response()->json([
            'error'=>$message,
            'code'=>$code,
        ]);
    }
}
