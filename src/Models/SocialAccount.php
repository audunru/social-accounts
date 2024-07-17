<?php

namespace audunru\SocialAccounts\Models;

use audunru\SocialAccounts\Factories\SocialAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property string                      $provider
 * @property string                      $provider_user_id
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class SocialAccount extends Model
{
    /** @use HasFactory<SocialAccountFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'provider',
        'provider_user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'provider'         => 'string',
        'provider_user_id' => 'string',
    ];

    /**
     * @param array<string, string> $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('social-accounts.table_names.social_accounts'));
    }

    /**
     * Return the user this social account belongs to.
     *
     * @return BelongsTo<Model, $this>
     */
    public function user(): BelongsTo
    {
        /** @var class-string<Model> $userModel */
        $userModel = config('social-accounts.models.user');

        return $this->belongsTo(
            $userModel,
            config('social-accounts.column_names.foreign_key'),
            config('social-accounts.column_names.primary_key')
        );
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): SocialAccountFactory
    {
        return SocialAccountFactory::new();
    }
}
