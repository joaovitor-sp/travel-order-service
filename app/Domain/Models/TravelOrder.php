<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use App\Domain\Enums\TravelOrderStatus;
use App\Domain\Models\User;
use DomainException;

class TravelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'destination',
        'departure_date',
        'return_date',
    ];

    protected $guarded = [
        'status',
    ];

    protected $casts = [
        'departure_date' => 'datetime',
        'return_date' => 'datetime',
        'status' => TravelOrderStatus::class,
    ];

    protected $attributes = [
        'status' => TravelOrderStatus::REQUESTED->value,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approve(): void
    {
        if ($this->status === TravelOrderStatus::APPROVED) {
            throw new DomainException("Travel order is already approved");
        }

        $this->status = TravelOrderStatus::APPROVED;
    }

    public function cancel(): void
    {
        if ($this->status === TravelOrderStatus::CANCELED) {
            throw new DomainException("Travel order is already canceled");
        }
        if ($this->status === TravelOrderStatus::APPROVED) {
            throw new DomainException("Cannot cancel approved order");
        }

        $this->status = TravelOrderStatus::CANCELED;
    }

    public function canUpdate(): void
    {
        if ($this->status !== TravelOrderStatus::REQUESTED) {
            throw new DomainException(
                "Cannot update order when status is '{$this->status->value}'"
            );
        }
    }

}