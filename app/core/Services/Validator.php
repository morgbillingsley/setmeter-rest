<?php

namespace Core\Services;

class Validator
{
    static function string(string $string, int $min = null, int $max = null): boolean
    {
        $length = strlen($string);
        if ($min !== null) {
            if ($strlen < $min) {
                return false;
            }
        }
        if ($max !== null) {
            if ($length > $max) {
                return false;
            }
        }

        return true;
    }

    static function email(string $email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    static function phone(string $phone)
    {
        if (preg_match("/^(\+[0-9]{1,3})?[- ]?\(?([0-9]{3})\)?[- ]?([0-9]{3})[- ]?([0-9]{4})$/", $phone) == 0) {
            return false;
        }

        return true;
    }

    static function strongPassword(string $password)
    {
        $length = strlen($password);
        if ($length > 7) {
            if (preg_match("/[A-Z]/", $password)) {
                if (preg_match("/[a-z]/", $password)) {
                    if (preg_match("/[0-9]/")) {
                        if (preg_match("/[\.\!\@\#\$\%\^\&\*\(\)\-\=\+\`\~\,\<\>\'\"\\\|\{\}\[\]]/", $password)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}

?>