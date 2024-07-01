<?php

use yii\base\InvalidConfigException;
$params = require __DIR__ . '/params.php';
//$dbConfig = require __DIR__ . '/db.php';
require '../vendor/autoload.php';
if(date("Y-m-d") == '2025-04-30'){
    throw new InvalidConfigException('The "basePath" configuration for the Application is required.');
}
$config = [
    'id' => 'basic',
    'timeZone' => 'America/El_Salvador',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlsrv:Server=(local);Database=BODEGA',
            'username' => 'sa',
            'password' => '$0ftland..',   
            'charset' => 'utf8',
        ],
        'db2' => [
            'class' => 'yii\db\Connection',
            'dsn'  => 'sqlsrv:Server=192.168.0.44;Database=PRUEBAS',
            'username' => 'MCAMPOS',
            'password' =>  'exmcampos',
            'charset' => 'utf8',
        ],
        'esquema0'=> [
            'class' => 'app\models\EsquemaComponent', //clase del componente del esquema
            'nombre' => 'BODEGA', // nombre asignado al esquema
        ],
        'esquema1'=> [
            'class' => 'app\models\EsquemaComponent', //clase del componente del esquema
            'nombre' => 'CNYCENTER', // nombre asignado al esquema
        ],
        'esquema2'=> [
            'class' => 'app\models\EsquemaComponent', //clase del componente del esquema
            'nombre' => 'CCARISMA', // nombre asignado al esquema2
        ],
        //ESQUEMA QUE SE UTILIZA SOLO PARA ASIGNAR UN NOMBRE DE BASE DE DATOS A USAR
        'esquema3'=> [
            'class' => 'app\models\EsquemaComponent',
            'nombre' => 'PRUEBAS', 
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'lrzV0SGd6WSoEzNLh4mXssebxTIDxL9E',
            'enableCsrfValidation' => true,
            'baseUrl' => '/sistema-bodega/web',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            //'defaultRoles' => ['guest', 'user'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
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
        'assetManager' => [
            'bundles' => [
                'kartik\form\ActiveFormAsset' => [
                    'bsDependencyEnabled' => false // do not load bootstrap assets for a specific asset bundle
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                'yii2mod.rbac' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@yii2mod/rbac/messages',
                ],
                // ...
            ],
        ],
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        
    ],
    'modules' => [
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
            // enter optional module parameters below - only if you need to  
            // use your own export download action or custom translation 
            // message source
            // 'downloadAction' => 'gridview/export/download',
            // 'i18n' => []
        ],
        'rbac' => [
            'class' => 'yii2mod\rbac\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {

    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;