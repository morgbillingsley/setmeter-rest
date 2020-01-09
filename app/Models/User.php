<?php

namespace Models;

use Doctrine\ORM\Mapping as ORM;
use \Firebase\JWT\JWT;
use Core\Services\Validator;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=180, unique=true, name="email")
     */
    private $email;

    /**
     * @ORM\Column(type="string", name="password")
     */
    private $password;

    /**
     * @ORM\Column(type="string", name="first_name", nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", name="last_name", nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", name="phone", nullable=true)
     */
    private $phone;

    /**
     * @ORM]Column(type="datetime", name="signed_up")
     */
    private $signedUp;

    /**
     * @ORM\Column(type="simple_array", name="roles", nullable=true)
     */
    private $roles;

    public function __construct()
    {
        $this->signedUp = new DateTime();
        $this->roles = [];
    }

    public function getId(): ?string
    {
        return $this->uuid;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        if ($this->email !== $email) {
            if (Validator::email($email)) {
                $this->email = $email;
            } else {
                throw new \Exception("Please enter a valid email address");
            }
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $encrypted = password_hash($password, PASSWORD_BCRYPT);
        if ($this->password !== $encrypted) {
            $this->password = $encrypted;
        }

        return $this;
    }

    public function setSafePassword(string $password, string $confirmPassword): self
    {
        if ($password === $confirmPassword) {
            $this->setPassword($password);
        } else {
            throw new \Exception("The passwords do not match.");
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        if ($this->firstName !== $firstName) {
            if ($firstName !== "") {
                $this->firstName = $firstName;
            } else {
                throw new \Exception("First Name cannot be blank.");
            }
        }

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        if ($this->lastName !== $lastName) {
            if ($lastName !== "") {
                $this->lastName = $lastName;
            } else {
                throw new \Exception("Last Name cannot be blank.");
            }
        }

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        if ($this->phone !== $phone) {
            if (Validator::phone($phone)) {
                $this->phone = $phone;
            } else {
                throw new \Exception("The phone number is invalid. It must follow the format xxx-xxx-xxxx");
            }
        }

        return $this;
    }

    public function getSignedUp(): ?DateTime
    {
        return $this->signedUp;
    }

    public function setSignedUp(DateTime $signedUp): self
    {
        if ($this->signedUp !== $signedUp) {
            $this->signedUp = $signedUp;
        }

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function getRoles(): ?array
    {
        $roles = $this->roles;
        $roles[] = 'user';

        return array_unique($roles);
    }

    public function addRole(string $role): self
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function createAccessToken(): ?string
    {
        if (!isset($this->uuid)) {
            return null;
        }
        $issuedAt = time();
        $payload = [
            "jti" => base64_encode(random_bytes(32)),
            "iss" => SITE_NAME,
            "iat" => $issuedAt,
            "nbf" => $issuedAt + 1,
            "exp" => $issuedAt + 3600,
            "data" => [
                "type" => "access",
                "uuid" => $this->uuid,
                "email" => $this->email,
                "isAdmin" => $this->hasRole("admin"),
            ]
        ];
        $jwt = JWT::encode($payload, API_KEY, "HS512");

        return $jwt;
    }

    public function createRefreshToken()
    {
        $issuedAt = time();
        $payload = [
            "jti" => base64_encode(random_bytes(32)),
            "iss" => SITE_NAME,
            "iat" => $issuedAt,
            "nbf" => $issuedAt + 1,
            "exp" => $issuedAt + 7200,
            "data" => [
                "type" => "refresh",
                "uuid" => $this->uuid
            ]
        ];
        $jwt = JWT::encode($payload, API_KEY, "HS512");

        return $jwt;
    }

    public function getTokens(): array
    {
        $accessToken = $this->createAccessToken();
        $refreshToken = $this->createRefreshToken();

        return [ "access_token" => $accessToken, "refresh_token" => $refreshToken ];
    }

    public function toArray(): ?array
    {
        $array = get_object_vars($this);
        unset($array["password"]);
        return $array;
    }

    public function set(array $data = null): self
    {
        $data = $data ? $data : $_POST;
        $this->setEmail($data["email"])
        ->setSafePassword($data["password"], $data["confirm_password"])
        ->setFirstName($data["first_name"])
        ->setLastName($data["last_name"])
        ->setPhone($data["phone"]);

        return $this;
    }
}