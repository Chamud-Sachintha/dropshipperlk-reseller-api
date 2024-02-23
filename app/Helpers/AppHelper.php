<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    public function generate_ref($length) {
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= random_int(0, 9);
        }

        return $result;
    }

    public function decodeImage($imageData) {

        $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
        $imageFileName = 'image_' . time() . Str::random(5) . '.png';

        // Storage::kyc('kyc')->put($imageFileName, $image);
        file_put_contents(public_path() . '/images' . '/' . $imageFileName, $image);

        return $imageFileName;
    }

    public function encodeImage($imageName) {
        $base64String = null;

        try {
            $path = public_path('avatar/' . $imageName);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64String = 'data:image/' . $type . ';base64,' . base64_encode($data);
        } catch (\Exception $e) {
            return false;
        }

        return $base64String;
    }

    public function getRootPath() {
        $path = "http://localhost:8000/";
        return $path;
    }
}

?>