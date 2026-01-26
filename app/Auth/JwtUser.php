<?php

namespace App\Auth;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable;

class JwtUser implements JWTSubject, Authenticatable
{
    public function __construct(private array $claims) {}

    public function getJWTIdentifier()
    {
        return $this->claims['sub'] ?? null;
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->claims['sub'] ?? null;
    }

    public function getAuthPassword() {}
    public function getAuthPasswordName() { return 'password'; }
    public function getRememberToken() {}
    public function setRememberToken($value) {}
    public function getRememberTokenName() {}

    public function __get($key)
    {
        return $this->claims[$key] ?? null;
    }

    public function toArray()
    {
        return $this->claims;
    }
}
