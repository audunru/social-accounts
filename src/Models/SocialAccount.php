<?php

namespace audunru\SocialAccounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    protected $fillable = [
        'provider',
        'provider_user_id',
    ];

    protected $casts = [
        'provider' => 'string',
        'provider_user_id' => 'string',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('social-accounts.table_names.social_accounts'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('social-accounts.models.user'),
            config('social-accounts.column_names.foreign_key'),
            config('social-accounts.column_names.primary_key')
        );
    }
}
