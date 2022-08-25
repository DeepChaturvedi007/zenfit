<?php

namespace AppBundle\Services;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidationService
{
    public function checkEmail($email)
    {
        $email = trim($email);
        if ((!filter_var($email, FILTER_VALIDATE_EMAIL)) || !$this->checkEmptyString($email)) {
          throw new HttpException(422, 'That is not a valid email.');
        }

        return true;
    }

    public function checkEmptyString($string, $msg = 'The name is empty.')
    {
        if ($string == "" || !$string) {
          throw new HttpException(422, $msg);
        }

        return true;
    }

    public function passwordValidation($pass1, $pass2)
    {
        if (empty($pass1) || empty($pass2)) {
            throw new HttpException(422, 'The password is empty.');
        }

        if (strcasecmp($pass1, $pass2) !== 0) {
            throw new HttpException(422, 'The passwords dont match.');
        }

        if (strlen($pass1) < 5) {
            throw new HttpException(422, 'The password is fewer than 5 characters.');
        }

        return true;
    }

    public function checkClientPassword($password, $actualPassword)
    {
        if(!password_verify($password, $actualPassword)) {
          throw new HttpException(422, 'The current password is wrong.');
        }

        return true;
    }

}
