<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,
    'key'=>[
        'jwt'=> env('KEY_JWT'),
        'jwt_private'=> env('KEY_JWT_PRIVATE'),
        'alg'=>'HS256',
        'expire'=>60*60*60, // second
        'expire_refresh_token'=>60*60*60*60,
    ],
    'platform_app'=>'app',
    'social'=>[
        'key'=> env('KEY_SOCIAL_AUTH'),
        'expire'=>60*60*60,
        'facebook'=>[
            'client_id'=>env('FACEBOOK_CLIENT_ID'),
            'redirect_uri'=>env('FACEBOOK_REDIRECT_URI'),
            'client_secret'=>env('FACEBOOK_CLIENT_SECRET'),
            'base_api'=>env('FACEBOOK_BASE_API'),
            'version'=>env('FACEBOOK_VERSION'),
            'scope'=>[
                'public_profile',
                'email'
            ]
        ],
        'google'=>[
            'client_id'=>env('GOOGLE_CLIENT_ID'),
            'redirect_uri'=>env('GOOGLE_REDIRECT_URI'),
            'client_secret'=>env('GOOGLE_CLIENT_SECRET'),
            'base_api'=>env('GOOGLE_BASE_API'),
            'version'=>env('GOOGLE_VERSION'),
            'scope'=>[
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/user.addresses.read',
                'https://www.googleapis.com/auth/user.birthday.read',
                'https://www.googleapis.com/auth/user.emails.read',
                'https://www.googleapis.com/auth/user.gender.read',
                'https://www.googleapis.com/auth/user.organization.read',
                'https://www.googleapis.com/auth/user.phonenumbers.read',
                'https://www.googleapis.com/auth/userinfo.profile'
            ]
        ],
        'github'=>[
            'app_id'=>env('GITHUB_APP_ID'),
            'host'=>env('GITHUB_HOST'),
            'client_id'=>env('GITHUB_CLIENT_ID'),
            'redirect_uri'=>env('GITHUB_REDIRECT_URI'),
            'client_secret'=>env('GITHUB_CLIENT_SECRET'),
            'base_api'=>env('GITHUB_BASE_API'),
            'version'=>env('GITHUB_VERSION'),
            'scope'=>[
                'user'
            ]
        ],

    ],
    'debug_key'=>env('DEBUG_TOKEN')
];
