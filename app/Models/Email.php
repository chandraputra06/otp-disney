<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Email extends Model
{
    protected $fillable = [
        'mailbox_id',
        'message_id',
        'sender_name',
        'sender_email',
        'subject',
        'body_text',
        'body_html',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function mailbox(): BelongsTo
    {
        return $this->belongsTo(Mailbox::class);
    }
}