<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'admin_users';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'email',
        'password_hash'
    ];

    protected $hidden = [
        'password_hash',
        'remember_token'
    ];

    protected $casts = [
        'id' => 'string',
        'email_verified_at' => 'datetime',
    ];

    // Tell Sanctum which field contains the password
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
