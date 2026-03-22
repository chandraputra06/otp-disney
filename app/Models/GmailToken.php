<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GmailToken extends Model
{
    protected $fillable = [
        'otp_account_id',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function otpAccount(): BelongsTo
    {
        return $this->belongsTo(OtpAccount::class);
    }
}