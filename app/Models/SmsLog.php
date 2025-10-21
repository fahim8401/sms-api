<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'user_id',
        'to',
        'senderid',
        'message',
        'cost',
        'status',
        'message_id',
        'delivery_text',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the SMS log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
