<?php

return [
    /*
     * Routes start with a prefix.
     */
    'route_prefix' => 'social-accounts',
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
         */
        'login-with-provider' => 'login-with-provider',
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
    /*
    * If a user is logged in, and then logs in with Socialite, should that account be added to their social accounts?
    */
    'users_can_add_social_accounts' => true,
    /*
     * If someone logs in succesfully with a provider, but they don't have a Laravel user already, should we create one?
     *
     * By default, users will have to sign up first, and only while they are signed in can they add social accounts to log in with.
     */
    'automatically_create_users' => false,
];
