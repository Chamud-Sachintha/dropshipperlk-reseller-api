<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Hash;

class AppHelper {

    public function responseMessageHandle($code, $message) {
        $data['code'] = $code;
        $data['message'] = $message;

        return $data;
    }

    public function responseEntityHandle($code, $msg, $response, $token = null) {

        $data['code'] = $code;
        $data['msg'] = $msg;
        $data['data'] = [$response];
        
        if ($token != null) {
            $data['token'] = $token;
        }

        return $data;
    }

    public function generateAuthToken($user) {
        $authCode = "CS Software Engineering" . $user . $this->day_time();
        return Hash::make($authCode);
    }

    public function day_time() {
        return strtotime(date("Ymd"));
    }

    public function get_date_and_time() {
        return strtotime("now");
    }
}

?>