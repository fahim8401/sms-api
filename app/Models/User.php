<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'api_key',
        'balance',
        'rate',
        'role',
        'parent_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'rate' => 'decimal:2',
        ];
    }

    /**
     * Get the parent user (reseller).
     */
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Get the sub-users under this reseller.
     */
    public function subUsers()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * Get SMS logs for this user.
     */
    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    /**
     * Get transactions for this user.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Generate unique API key for user.
     */
    public static function generateApiKey()
    {
        return bin2hex(random_bytes(16));
    }
}
