<?php

namespace AppBundle\Lib;

class UserHelper {

    public static function stringRand($length = 3) {
        $string = bin2hex(openssl_random_pseudo_bytes($length));

        return $string;
    }


}