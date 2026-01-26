<?php

namespace App\Domain\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Domain\Models\TravelOrder;

class User extends Authenticatable implements JWTSubject
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'is_admin'];
    protected $casts = ['is_admin' => 'boolean'];

    // JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'name' => $this->name,
            'is_admin' => $this->is_admin,
        ];
    }

    public function travelOrders()
    {
        return $this->hasMany(TravelOrder::class);
    }
}
