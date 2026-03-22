<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpMessage extends Model
{
    protected $fillable = [
        'otp_account_id',
        'message_id',
        'sender_email',
        'subject',
        'email_snippet',
        'otp_code',
        'fetched_status',
        'received_at',
        'raw_payload',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function otpAccount(): BelongsTo
    {
        return $this->belongsTo(OtpAccount::class);
    }
}