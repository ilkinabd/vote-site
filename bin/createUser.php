<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Classes\BackendUser;
use Classes\BackendUserProvider;
use Doctrine\DBAL\Connection;
use Lokhman\Silex\Provider\ConfigServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

$app = new Silex\Application();

ini_set('memory_limit', '-1');
date_default_timezone_set('Europe/Moscow');


$app['debug'] = true;

function arguments($argv)
{
    $_ARG = array();
    foreach ($argv as $arg) {
        if (ereg('--([^=]+)=(.*)', $arg, $reg)) {
            $_ARG[$reg[1]] = $reg[2];
        } elseif (ereg('-([a-zA-Z0-9])', $arg, $reg)) {
            $_ARG[$reg[1]] = 'true';
        }
    }
    return $_ARG;
}

// Config
$app->register(new ConfigServiceProvider(), [
    'config.dir' => __DIR__ . '/../config',
]);

// Doctrine
$app->register(new DoctrineServiceProvider(), [
    'db.options' => $app['config']['database']
]);

// Security
$app->register(new SecurityServiceProvider(), [
    'security.firewalls' => [
        'backend_login' => [
            'pattern' => '^/admin/login$'
        ],
        'backend' => [
            'pattern' => '^/(admin|ajax)',
            'form' => [
                'login_path' => '/admin/login',
                'check_path' => '/admin/login_check'
            ],
            'logout' => [
                'logout_path' => '/admin/logout',
                'invalidate_session' => true
            ],
            'users' => function () use ($app) {
                return new BackendUserProvider($app['db']);
            }
        ]
    ]
]);

/** @var Connection $db */
$db = $app['db'];

$arguments = arguments($argv);

try {
    // $user = new BackendUser($arguments['login'], $arguments['pass'],
    //     $arguments['name'], $arguments['surname'], ['ROLE_MANAGER']);

    // /** @var BCryptPasswordEncoder $encoder */
    // $encoder = $app['security.encoder_factory']->getEncoder($user);

    // // Encode password
    // $userPassword = $encoder->encodePassword($user->getPassword(), $user->getSalt());
    $res = $db->executeQuery('SELECT external_id,
    first_name,
    last_name,
    voted,
    singer_id,
    auth_type,
    last_login,
    last_logout,
    last_voted FROM ordinary_user ORDER BY RAND() limit 1032;');
    while ($row = $res->fetch()) {
        echo $row['first_name'] . "\n";
        $db->insert('ordinary_user', [
            'external_id' => $row['external_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'voted' => 1,
            'singer_id' => 29,
            'auth_type' => $row['auth_type'],
            'last_login' => $row['last_login'],
            'last_logout' => $row['last_logout'],
            'last_voted' => $row['last_voted']
        ]);
    }
    // $db->insert('backend_user', [
    //     'user_login' => $user->getUsername(),
    //     'user_pass' => $userPassword,
    //     'user_name' => $user->getName(),
    //     'user_surname' => $user->getSurname(),
    //     'user_roles' => implode(',', $user->getRoles())
    // ]);

    echo 'success';
} catch (Exception $exception) {
    echo $exception->getMessage();
}
