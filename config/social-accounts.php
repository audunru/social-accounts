<?php

return [
    /*
     * Web routes start with a prefix.
     */
    'route_prefix' => 'social-accounts',

    /*
     * API endpoint path.
     */
    'api_path' => 'social-accounts',

    /*
     * Enable automatic registration of web routes (used for authentication).
     */
    'web_routes_enabled' => true,

    /*
     * Web middleware.
     */
    'web_middleware' => ['web'],

    /*
     * Enable automatic registration of API routes (used for retrieving social accounts).
     */
    'api_routes_enabled' => true,

    /*
     * API middleware.
     */
    'api_middleware' => ['api'],

    /*
     * If someone logs in succesfully with a provider, but they don't have a Laravel user already, should we create one?
     *
     * By default, users will have to sign up first, and only while they are signed in can they add social accounts to log in with.
     */
    'automatically_create_users' => false,

    /*
     * Enable the providers you want, and then configure credentials for each of them in config/services.php according to the Socialite documentation.
     */
    'providers' => [
        // 'bitbucket',
        // 'facebook',
        // 'github',
        // 'gitlab',
        'google',
        // 'linkedin',
        // 'twitter',
    ],

    /*
     * Gates are used to allow or deny actions.
     *
     * You can rename them if you would like to use a different name.
     */
    'gates' => [
        /*
         * The "login-with-provider" gate check is run after a user has authorized with a provider, but before they are logged in or we have created an account for them.
         *
         * The gate receives the current user (or "null" if unauthenticated, which would typically be the case) as its first argument, and a ProviderUser instance as its second.
         *
         * Guest User Gates are only available in Laravel 5.7 and up.
         */
        'login-with-provider' => 'login-with-provider',

        /*
         * The "add-social-account" gate check is run before a social account is added to a user.
         *
         * The gate receives the current user as its first argument, and a ProviderUser instance as its second.
         */
        'add-social-account' => 'add-social-account',
    ],
    'models' => [
        /*
         * When using the "HasSocialAccounts" trait from this package, we need to know which Eloquent model should be used to retrieve your social accounts.
         */
        'social_account' => audunru\SocialAccounts\Models\SocialAccount::class,

        /*
         * When using the "HasSocialAccounts" trait from this package, we need to know which Eloquent model the social accounts belong to.
         */
        'user' => App\User::class,
    ],
    'table_names' => [
        /*
         * When using the "HasSocialAccounts" trait from this package, we need to know which table should be used to retrieve your social accounts.
         */
        'social_accounts' => 'social_accounts',

        /*
         * When running the migrations, we need to know which table contains your users, so we can make the "email" and "password" columns nullable.
         */
        'users' => 'users',
    ],
    'column_names' => [
        /*
         * Change this if you want to name the foreign key in the "social_accounts" table anything other than `user_id`.
         */
        'foreign_key' => 'user_id',

        /*
         * Change this if you want to name the parent model primary key anything other than `id`.
         */
        'primary_key' => 'id',
    ],
    'user_id_column' => [
        /*
         * Change this if the id column in your users table is not the default type.
         */
        'type' => 'bigInteger',

        /*
         * Change this if the id column in your users table is not unsigned.
         */
        'unsigned' => true,
    ],
];
