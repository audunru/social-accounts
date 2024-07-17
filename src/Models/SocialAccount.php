<?php

namespace audunru\SocialAccounts\Models;

use audunru\SocialAccounts\Factories\SocialAccountFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template TUserModel of \Illuminate\Database\Eloquent\Model
 */
class SocialAccount extends Model
{
    /** @use HasFactory<SocialAccountFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider',
        'provider_user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'provider'         => 'string',
        'provider_user_id' => 'string',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('social-accounts.table_names.social_accounts'));
    }

    /**
     * Return the user this social account belongs to.
     *
     * @return BelongsTo<TUserModel, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('social-accounts.models.user'),
            config('social-accounts.column_names.foreign_key'),
            config('social-accounts.column_names.primary_key')
        );
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return SocialAccountFactory::new();
    }
}
