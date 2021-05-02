<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;



trait CommonUtil {

    public function ValidationResponseFormating($e) {
        $errorResponse = [];
        $errors = $e->validator->errors();
        $col = new Collection($errors);
        foreach ($col as $error) {
            foreach ($error as $errorString) {
                $errorResponse[] = $errorString;
            }
        }
        return $errorResponse;
    }

    public function getCurrentTime($timezone){
       return  now()->setTimezone($timezone);
    }
}
