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
            'enableStrictParsing' => false,
            'rules' => [
                // ...
            ],
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
                /*'google' => [
                    'class' => 'yii\authclient\clients\GoogleOAuth',
                    'clientId' => 'google_client_id',
                    'clientSecret' => 'google_client_secret',
                ],
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                    'clientId' => '803296753079112',
                    'clientSecret' => 'facebook_client_secret',
                    'returnUrl' => 'http://pplsrch.localhost'
                ],*/
                'vkontakte' => [
                    'class' => 'yii\authclient\clients\VKontakte',
                    'clientId' => '4845041',
                    'clientSecret' => '5J3vGkexmhBGqvg9CF4f',
                    'returnUrl' => 'http://pplsrch.localhost/index.php?r=site/auth&authclient=vkontakte',
                    'scope' => 'friends'
                ],
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                    'clientId' => '803296753079112',
                    'clientSecret' => '029a8d2cccc3330261ee7fdc28b7b1aa',
                    //'returnUrl' => 'http://pplsrch.localhost/index.php?r=site/auth&authclient=facebook'
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
