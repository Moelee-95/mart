<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
    public $fillable = [
        'phone',
        'token',
        'expires_at',
        'registered'
    ];
}
