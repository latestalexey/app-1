<?php


$common = [
    'id'          => 'app',
    'basePath'    => dirname(__DIR__),
    'bootstrap'   => ['log'],
    'vendorPath'  => '@app/../vendor',
    'runtimePath' => '@app/../runtime',
    'aliases'     => [
        '@admin-views' => '@app/modules/backend/views'
    ],
    'components'  => [
        'assetManager' => [
            'dirMode'    => YII_ENV_PROD ? 0777 : null, // Note: For using mounted volumes or shared folders
            'bundles'    => YII_ENV_PROD ? require(__DIR__ . '/assets-gen/prod.php') : null,
            'basePath' => '@root',
        ],
        'authManager'  => [
            'class' => 'yii\rbac\DbManager',
        ],
        'cache'        => [
            'class' => 'yii\caching\FileCache',
        ],
        'db'           => [
            'class'       => 'yii\db\Connection',
            'dsn'         => getenv('DATABASE_DSN'),
            'username'    => getenv('DATABASE_USER'),
            'password'    => getenv('DATABASE_PASSWORD'),
            'charset'     => 'utf8',
            'tablePrefix' => getenv('DATABASE_TABLE_PREFIX'),
        ],
        'mailer'       => [
            'class'            => 'yii\swiftmailer\Mailer',
            //'viewPath'         => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => YII_ENV_PROD ? false : true,
        ],
        'urlManager'   => [
            'enablePrettyUrl' => getenv('APP_PRETTY_URLS') ? true : false,
            'showScriptName'  => getenv('YII_ENV_TEST') ? true : false,
            'baseUrl'         => '/',
            'rules'           => [
                'docs/<file:[a-zA-Z0-9_\-\.]*>' => 'docs',
            ],
        ],
        'view'         => [
            'theme' => [
                'pathMap' => [
                    '@vendor/dektrium/yii2-user/views/admin' => '@app/views/user/admin',
                    '@yii/gii/views/layouts'                 => '@admin-views/layouts',
                ],
            ],
        ],

    ],
    'modules'     => [
        'backend' => [
            'class'  => 'app\modules\backend\Module',
            'layout' => '@admin-views/layouts/main',
        ],
        /*'docs'    => [
            'class'  => \schmunk42\markdocs\Module::className(),
            'layout' => '@app/views/layouts/container',
        ],*/
        'pages' => [
            'class'  => \dmstr\modules\pages\Module::className(),
            'layout' => '@admin-views/layouts/main',
        ],
        'user'  => [
            'class'        => 'dektrium\user\Module',
            'layout'       => '@app/views/layouts/container',
            'defaultRoute' => 'profile',
            'admins'       => ['admin']
        ],
        'rbac'  => [
            'class'  => 'dektrium\rbac\Module',
            'layout' => '@admin-views/layouts/main',
        ],
        'pages' => [
            'class'  => \dmstr\modules\pages\Module::className(),
            'layout' => '@admin-views/layouts/main',
        ],
        'treemanager' => [
            'class'  => '\kartik\tree\Module',
            'layout' => '@admin-views/layouts/main',
        ],
    ],
    'params'      => [
        'appName'        => getenv('APP_NAME'),
        'adminEmail'     => getenv('APP_ADMIN_EMAIL'),
        'supportEmail'   => getenv('APP_SUPPORT_EMAIL'),
        'yii.migrations' => [
            '@yii/rbac/migrations',
            '@dektrium/user/migrations',
        ]
    ]

];


$web = [
    'components' => [
        // Logging
        'log'     => [
            'targets' => [
                // writes to php-fpm output stream
                // writes to php-fpm output stream
                [
                    'class'   => 'codemix\streamlog\Target',
                    'url'     => 'php://stdout',
                    'levels'  => ['info', 'trace'],
                    'logVars' => [],
                ],
                [
                    'class'   => 'codemix\streamlog\Target',
                    'url'     => 'php://stderr',
                    'levels'  => ['error', 'warning'],
                    'logVars' => [],
                ],
            ],
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => getenv('APP_COOKIE_VALIDATION_KEY'),
        ],
        'user'    => [
            'identityClass' => 'dektrium\user\models\User',
        ],
    ]
];

$console = [
    'controllerNamespace' => 'app\commands',
    'controllerMap'       => [
        'migrate' => 'dmstr\console\controllers\MigrateController',
        'yaml'    => 'dmstr\console\controllers\DockerStackConverterController'
    ],
    'components'          => [

    ]
];


$allowedIPs = [
    '127.0.0.1',
    '::1',
    '192.168.*',
    '172.17.*'
];

// detecting current application type based on `php_sapi_name()` since we've no application ready yet.
if (php_sapi_name() == 'cli') {
    // Console application
    $config = \yii\helpers\ArrayHelper::merge($common, $console);
} else {
    // Web application
    if (YII_ENV_DEV) {
        // configuration adjustments for web 'dev' environment
        $common['bootstrap'][]      = 'debug';
        $common['modules']['debug'] = [
            'class'      => 'yii\debug\Module',
            'allowedIPs' => $allowedIPs
        ];
    }
    $config = \yii\helpers\ArrayHelper::merge($common, $web);
}

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][]    = 'gii';
    $config['modules']['gii'] = [
        'class'      => 'yii\gii\Module',
        'allowedIPs' => $allowedIPs
    ];
}

if (file_exists(__DIR__ . '/local.php')) {
    // Local configuration, if available
    $local  = require(__DIR__ . '/local.php');
    $config = \yii\helpers\ArrayHelper::merge($config, $local);
}

return $config;
