<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $fillable = [ 
        'username', 'name', 'office', 'designation', 'email', 'password', 'role', 'is_active'
    ];

    protected $hidden = ['password'];
}
