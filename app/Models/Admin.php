<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use Notifiable;
    protected $fillable = [
        'username',
        'name',
        'office',
        'designation',
        'email',
        'password',
        'role',
        'is_active'
    ];

    protected $hidden = ['password'];

    public function preferences()
    {
        return $this->hasMany(AdminNotificationPreference::class);
    }

    public function wantsNotification($type)
    {
        // Default to true if no preference is set
        $preference = $this->preferences()->where('notification_type', $type)->first();
        return $preference ? $preference->is_enabled : true;
    }
}
