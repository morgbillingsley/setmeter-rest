<?php

use \Firebase\JWT\JWT;
use Models\User;
use Core\Services\Token;

class Controller
{
    public function __construct()
    {
        global $entityManager;
        $this->em = $entityManager;
        $this->headers = apache_request_headers();
    }

    public function send($data): int
    {
        $type = gettype($data);
        if ($type === "array") {
            header("Content-Type: application/json");
            $response = json_encode($data);
        } else if ($type === "string") {
            header("Content-Type: text/plain");
            $response = $data;
        }
        echo($response);

        return 0;
    }

    public function sendFile(string $path)
    {
        if (!file_exists($path)) {
            throw new Error("The file could not be found.");
        }
        $mime = mime_content_type($path);
        header("Content-Type: " . $mime);
        echo file_get_contents($path);

        return 0;
    }

    public function error(Exception $error, int $code=500)
    {
        http_response_code($code);
        $this->send([
            "status" => "failed",
            "message" => $error->getMessage()
        ]);

        exit(0);
    }

    public function notFound()
    {
        $error = new \Exception("The page you are looking for cannot be found.");
        $this->error($error, 404);
    }

    public function isPost(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === "POST";
    }

    public function authorize(string $role = "user"): ?bool
    {
        $error = new \Exception("You are not allowed to view this page.");
        try {
            $user = Token::getUserFromToken();
            if ($user->hasRole($role)) {
                return true;
            }
        } catch (\Exception $e) {
            $error = $e;
        }
        $this->error($error, 401);
    }
}

?>