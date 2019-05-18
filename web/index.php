<?php
$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__ . '/../vendor/autoload.php';

use Classes\BackendAjaxControllerProvider;
use Classes\BackendControllerProvider;
use Classes\BackendUserProvider;
use Classes\FrontendControllerProvider;
use Classes\OauthServiceProvider;
use Classes\User;
use Doctrine\DBAL\Connection;
use Silex\Provider\SerializerServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Lokhman\Silex\Provider\ConfigServiceProvider;

$app = new Silex\Application();

ini_set('memory_limit', '-1');
date_default_timezone_set('Europe/Moscow');

define('HAS_NOT_PERMISSION_ERROR', 1);
define('ALREADY_VOTED_ERROR', 2);
define('POLL_STOPPED_ERROR', 3);

$app['debug'] = true;

// Config
$app->register(new ConfigServiceProvider(), [
    'config.dir' => __DIR__ . '/../config',
]);

$app['site_url'] = $app['config']['protocol'] . '://' . $app['config']['domain'] . ':' . $app['config']['port'];
$app['redirect_uri'] = $app['site_url'];
$app['asset_path'] = $app['site_url'] . '/front';
$app['vue_path'] = $app['site_url'] . '/back';
$app['music_path'] = $app['site_url'] . '/music';

// Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => $app['config']['database']
]);

// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../pages'
]);

// Serializer
$app->register(new SerializerServiceProvider(), [
    'serializer.normalizers' => function () use ($app) {
        return [
            new ObjectNormalizer(),
        ];
    }
]);

// Oauth
$app->register(new OauthServiceProvider(), [
    'oauth.config.redirect_uri' => $app['redirect_uri'],
    'oauth.config.socials' => $app['config']['socials']
]);

// Security
$app->register(new Silex\Provider\SecurityServiceProvider(), [
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

// Session
$app->register(new Silex\Provider\SessionServiceProvider());

$app->before(function (Request $request) use ($app) {

    $get = $request->query->all();

    /** @var SessionInterface $session */
    $session = $app['session'];
    $app['twig']->addGlobal('current_page', $request->getRequestUri());
    if (isset($get['code']) && $session->has('auth_social') && !$session->has('user')) {

        /** @var \Classes\Oauth $oauth */
        $oauth = $app['oauth'];

        /** @var Connection $db */
        $db = $app['db'];

        /** @var Serializer $serializer */
        $serializer = $app['serializer'];
        $socialName = $session->get('auth_social');
        $code = $get['code'];

        $userInfo = $oauth->authorize($socialName, $code);

        if ($userInfo) {

            // Check if user exists in db
            $id = $db->fetchColumn('SELECT id FROM ordinary_user WHERE external_id = ? and auth_type = ?', [
                $userInfo['external_id'],
                $socialName
            ], 0);

            // If not exists insert new user record
            if (!$id) {
                $db->insert('ordinary_user', [
                    'external_id' => $userInfo['external_id'],
                    'first_name' => $userInfo['first_name'],
                    'last_name' => $userInfo['last_name'],
                    'avatar' => $userInfo['avatar'],
                    'auth_type' => $socialName,
                    'last_login' => (new DateTime('NOW'))->format('Y-m-d H:i:s')
                ]);
            } else {
                // Update last_login
                $db->update('ordinary_user', [
                    'last_login' => (new DateTime('NOW'))->format('Y-m-d H:i:s')
                ], [
                    'id' => $id
                ]);
            }

            // Fetch user from db
            $res = $db->executeQuery('SELECT * FROM ordinary_user WHERE external_id = ? and auth_type = ?', array(
                $userInfo['external_id'],
                $socialName
            ));

            $userRecord = $res->fetch();

            // If user exists in db
            if ($userRecord) {
                /** @var User $user */
                $user = $serializer->denormalize($userRecord, User::class);
                $user->setAvatar($userInfo['avatar']);
                $session->set('user', $user);
                $app->redirect($app["url_generator"]->generate('main'));
            }
        }
    }
    return 0;
});

$app->mount('/', new FrontendControllerProvider());

$app->mount('/admin', new BackendControllerProvider());

$app->mount('/ajax', new BackendAjaxControllerProvider());

$app->run();