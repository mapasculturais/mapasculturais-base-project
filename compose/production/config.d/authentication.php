<?php
return [
    'auth.provider' => '\MultipleLocalAuth\Provider',
    'auth.config' => array(
        'salt' => env('AUTH_SALT', null),
        'timeout' => '24 hours',
        'strategies' => [
           'Facebook' => array(
               'app_id' => env('AUTH_FACEBOOK_APP_ID', null),
               'app_secret' => env('AUTH_FACEBOOK_APP_SECRET', null),
               'scope' => env('AUTH_FACEBOOK_SCOPE', 'email'),
           ),

            'LinkedIn' => array(
                'api_key' => env('AUTH_LINKEDIN_API_KEY', null),
                'secret_key' => env('AUTH_LINKEDIN_SECRET_KEY', null),
                'redirect_uri' => APP_BASE_URL . 'autenticacao/linkedin/oauth2callback',
                'scope' => env('AUTH_LINKEDIN_SCOPE', 'r_emailaddress')
            ),
            'Google' => array(
                'client_id' => env('AUTH_GOOGLE_CLIENT_ID', null),
                'client_secret' => env('AUTH_GOOGLE_CLIENT_SECRET', null),
                'redirect_uri' => APP_BASE_URL . 'autenticacao/google/oauth2callback',
                'scope' => env('AUTH_GOOGLE_SCOPE', 'email'),
            ),
            'Twitter' => array(
                'app_id' => env('AUTH_TWITTER_APP_ID', null),
                'app_secret' => env('AUTH_TWITTER_APP_SECRET', null),
            ),

        ]
    ),
];
