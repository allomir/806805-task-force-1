<?php
return [
    'language' => 'ru-RU', 
    'timeZone' => 'Europe/Moscow', 
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'defaultRoute' => 'start/index', 
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'errorHandler' => [
            'errorAction' => 'start/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false, // true - явно разрешенные URL в rules, напрмер '/' => '/' должен быть явно разрешен
            'normalizer' => [
                'class' => 'yii\web\UrlNormalizer',
                'action' => yii\web\UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
            ],
            'rules' => [
                [
                    'pattern' => '<controller:(task|user)>/view/<id>',
                    'route' => '<controller>s/view',
                ],
                '<controller:(tasks|users)>' => '<controller>/index',
            ],
        ],
    ],
    'params' => [
        'defaultAvatarAddr' => 'img/man-glasses.jpg',
    ]
];
