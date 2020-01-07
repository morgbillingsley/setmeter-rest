<?php

namespace Core\Services;

use \Firebase\JWT\JWT;
use Models\User;



class Token
{
    public static function getToken(): ?string
    {
        $headers = apache_request_headers();
        if (isset($headers["Authorization"])) {
            $authHeader = trim($headers["Authorization"]);
        } else {
            throw new \Exception("There is no Authorization header set.");
        }
        $regex = "/Bearer\s((.*)\.(.*)\.(.*))/";
        if (preg_match($regex, $authHeader, $matches)) {
            return $matches[1];
        } else {
            throw new \Exception("The provided token is invalid.");
        }

        return null;
    }

    public static function getUserFromToken(string $type = "access")
    {
        global $entityManager;
        $jwt = self::getToken();
        JWT::$leeway = 60;
        $decoded = JWT::decode($jwt, API_KEY, [ "HS512" ]);
        $userRepository = $entityManager->getRepository(User::class);
        if ($decoded->data->type === $type) {
            $user = $userRepository->findOneBy([ "uuid" => $decoded->data->uuid ]);
        } else {
            $error = new \Exception("You are using the " . $decoded->data->type . " token when you should be using the " . $type . " token.");
            throw $error;
        }

        return $user;
    }

    public static function newToken(array $data = null)
    {
        global $entityManager;
        $credentials = $data ? $data : $_POST;
        if (isset($credentials["email"]) && isset($credentials["password"])) {
            $userRepository = $entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy([ "email" => $_POST["email"] ]);
            $error = new \Exception("There is no user with the given email and password");
            if ($user !== null) {
                if (password_verify($_POST["password"], $user->getPassword())) {
                    return $user->getTokens();
                }
            }
        } else {
            $error = new \Exception("To get your access token, post the corresponding email and password for your account to this endpoint.");
        }
        throw $error;
    }

    public static function refresh()
    {
        $user = self::getUserFromToken("refresh");
        return $user->getTokens();
    }
}

?>