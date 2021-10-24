<?php

namespace audunru\SocialAccounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    /*
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider',
        'provider_user_id',
    ];

    /*
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'provider'         => 'string',
        'provider_user_id' => 'string',
    ];

    /*
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('social-accounts.table_names.social_accounts'));
    }

    /**
     * Return the user this social account belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('social-accounts.models.user'),
            config('social-accounts.column_names.foreign_key'),
            config('social-accounts.column_names.primary_key')
        );
    }
}
