<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable; 


class Customer extends Authenticatable 
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'full_name',
        'phone',
        'pin',
    ];

    protected $hidden = [
        'pin',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
