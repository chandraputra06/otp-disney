<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OtpAccount extends Model
{
    protected $fillable = [
        'phone_number',
        'gmail_address',
        'account_name',
        'is_active',
        'last_checked_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(OtpMessage::class);
    }

    public function gmailToken(): HasOne
    {
        return $this->hasOne(GmailToken::class);
    }
}