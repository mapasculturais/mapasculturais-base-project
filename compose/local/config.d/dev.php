<?php 

return [
    'doctrine.isDev' => false,


    // LOG --------------------
    'slim.log.level'        => \Slim\Log::DEBUG,
    'slim.log.enabled'      => true,

    // app.log.hook aceita regex para filtrar quais hooks são exibidos no output, 
    // ex: "panel", "^template", "template\(site\.index\.*\):before"
    'app.log.hook'          => false, 
    // 'app.log.query'         => true,
    // 'app.log.requestData'   => true,
    // 'app.log.translations'  => true,
    // 'app.log.apiCache'      => true,
    // 'app.log.apiDql'        => true,
    // 'app.log.assets'        => true,


    // MAILER -----------------
    // 'mailer.user'       => 'you@gmail.com', 
    // 'mailer.psw'        => 'passwd', 
    // 'mailer.protocol'   => 'SSL', 
    // 'mailer.server'     => 'smtp.gmail.com', 
    // 'mailer.port'       => '465', 
    // 'mailer.from'       => 'you@gmail.com', 
    // 'mailer.alwaysTo'   => 'you@gmail.com', // todos os emails serão enviados para este endereço


    // AUTH -------------------
    // 'auth.provider' => 'Fake', 
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
                'redirect_uri' => '/autenticacao/linkedin/oauth2callback',
                'scope' => env('AUTH_LINKEDIN_SCOPE', 'r_emailaddress')
            ),
            'Google' => array(
                'client_id' => env('AUTH_GOOGLE_CLIENT_ID', null),
                'client_secret' => env('AUTH_GOOGLE_CLIENT_SECRET', null),
                'redirect_uri' => '/autenticacao/google/oauth2callback',
                'scope' => env('AUTH_GOOGLE_SCOPE', 'email'),
            ),
            'Twitter' => array(
                'app_id' => env('AUTH_TWITTER_APP_ID', null),
                'app_secret' => env('AUTH_TWITTER_APP_SECRET', null),
            ),

        ]
    )
];