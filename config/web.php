<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'NXpMoQbj9pEXuBkd79DZJwptmv4biEQg',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        /*'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],*/
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['site/index'],
            'returnUrl' => ['search/index']
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'google' => [
                    'class' => 'app\components\authclient\clients\PSGoogleOAuth',
                    'clientId' => '17017104160-jdl67k3sm97psn76n1201gbs9nbuupoe.apps.googleusercontent.com',
                    'clientSecret' => 'RFQjn_pRbIHYEBGdVFc8PrX3',
                    //'returnUrl' => 'http://pplsrch.localhost/index.php?r=site%2Fauth&authclient=google',
                    //'returnUrl' => 'http://dev.pplsrch.in/index.php?r=site%2Fauth&authclient=google',
                ],
                'twitter' => [
                    'class' => 'app\components\authclient\clients\PSTwitter',
                    'consumerKey' => 'LxvEt937RYA4iN9Mn8gToacwz',
                    'consumerSecret' => 'N8hFBG9CTun9h6b8xiJG4wT8FFntulsfQ018QC6v6OWA8NuHHe',
                    //'returnUrl' => 'http://pplsrch.localhost/index.php?r=site%2Fauth&authclient=twitter',
                    'returnUrl' => 'http://dev.pplsrch.in/index.php?r=site%2Fauth&authclient=twitter',
                ],
                'linkedin' => [
                    'class' => 'app\components\authclient\clients\PSLinkedIn',
                    'clientId' => '77b9xleimd4wy0',
                    'clientSecret' => '6moaGW5HNgVvjBBv',
                    //'returnUrl' => 'http://pplsrch.localhost/index.php?r=site/auth&authclient=linkedin'
                    'returnUrl' => 'http://dev.pplsrch.in/index.php?r=site/auth&authclient=linkedin',
                ],
                'vkontakte' => [
                    'class' => 'app\components\authclient\clients\PSVKontakte',
                    /*People Search*/
                    //'clientId' => '4845041',
                    //'clientSecret' => '5J3vGkexmhBGqvg9CF4f',
                    //'returnUrl' => 'http://dev.pplsrch.in/index.php?r=site/auth&authclient=vkontakte',
                    
                    /*People Search Dev*/
                    'clientId' => '4859604',
                    'clientSecret' => 'Scw6dBG9zVqPKwC4ykrK',
                    'returnUrl' => 'http://pplsrch.localhost/index.php?r=site/auth&authclient=vkontakte',
                    'scope' => 'friends',
                ],
                'facebook' => [
                    'class' => 'app\components\authclient\clients\PSFacebook',
                    /*People Search*/
                    //'clientId' => '803296753079112',
                    //'clientSecret' => '029a8d2cccc3330261ee7fdc28b7b1aa',
                    //'returnUrl' => 'http://dev.pplsrch.in/index.php?r=site%2Fauth&authclient=facebook',

                    /*People Search Dev*/
                    'clientId' => '883568988361551',
                    'clientSecret' => '75d3ea44742dfabbabc8e9fd17044e10',
                    'returnUrl' => 'http://pplsrch.localhost/index.php?r=site%2Fauth&authclient=facebook',
                ]
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
