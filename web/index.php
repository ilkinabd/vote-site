<?php
$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__ . '/../vendor/autoload.php';

use Classes\OauthServiceProvider;
use Classes\User;
use Doctrine\DBAL\Connection;
use Silex\Provider\SerializerServiceProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Twig\Environment;
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

$app['asset_path'] = $app['config']['protocol'] . '://' . $app['config']['domain'] . ':' . $app['config']['port'] . '/front';
$app['vue_path'] = $app['config']['protocol'] . '://' . $app['config']['domain'] . ':' . $app['config']['port'] . '/back';
$app['music_path'] = $app['config']['protocol'] . '://' . $app['config']['domain'] . ':' . $app['config']['port'] . '/music';

// Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $app['config']['database'],
));

// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../pages',
));

// Serializer
$app->register(new SerializerServiceProvider(), []);

// Oauth
$app->register(new OauthServiceProvider(), [
    'oauth.config.socials' => [
        'vk' => [
            'url' => 'http://oauth.vk.com/authorize',
            'clientId' => '6474619',
            'clientSecret' => 'nkjtcKB9h0SaudVH4KFd',
            'redirectUri' => $app['config']['protocol'].'://' . $app['config']['domain'] . ':' . $app['config']['port'],
            'responseType' => 'code',
        ],
        'ok' => [
            'url' => 'http://www.odnoklassniki.ru/oauth/authorize',
            'clientId' => '1266500608',
            'clientSecret' => '515319EA34270B415395F34A',
            'grantType' => 'authorization_code',
            'redirectUri' => $app['config']['protocol'].'://' . $app['config']['domain'] . ':' . $app['config']['port'],
            'responseType' => 'code',
        ],
        'mr' => [
            'url' => 'https://connect.mail.ru/oauth/authorize',
            'clientId' => '760118',
            'clientSecret' => 'nkjtcKB9h0SaudVH4KFd',
            'redirectUri' => $app['config']['protocol'].'://' . $app['config']['domain'] . ':' . $app['config']['port'],
            'responseType' => 'code',
        ],
        'gp' => [
            'url' => 'https://accounts.google.com/o/oauth2/auth',
            'clientId' => '1014673555154-iguhfljk14clja6j8acjftg144t0i4es.apps.googleusercontent.com',
            'clientSecret' => 'nkjtcKB9h0SaudVH4KFd',
            'redirectUri' => $app['config']['protocol'].'://' . $app['config']['domain'] . ':' . $app['config']['port'],
            'responseType' => 'code',
            'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile'
        ]
    ]
]);

// Security
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => [
        'admin' => array(
            'pattern' => '^/admin',
            'form' => ['login_path' => '/login', 'check_path' => '/admin/login_check'],
            'logout' => ['logout_path' => '/admin/logout', 'invalidate_session' => true],
            'users' => [
                'admin' => [
                    'ROLE_ADMIN',
                    '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a'
                ]
            ]
        ),
    ]
));

// Session
$app->register(new Silex\Provider\SessionServiceProvider());

$app['serializer.normalizers'] = function () use ($app) {
    return [
        new ObjectNormalizer(),
    ];
};

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

// Logout
$app->get('/logout', function () use ($app) {
    /** @var SessionInterface $session */
    $session = $app['session'];
    /** @var Connection $db */
    $db = $app['db'];

    if ($session->has('user')) {
        /** @var User $user */
        $user = $session->get('user');
        $db->update('ordinary_user', [
            'last_logout' => (new DateTime('NOW'))->format('Y-m-d H:i:s')
        ], [
            'id' => $user->getId()
        ]);
        $session->remove('user');
    }
    return $app->redirect('/');
});

// Admin index page
$app->get('/admin', function () use ($app) {

    return $app->redirect('/admin/typo');
})->bind('admin_index');

// Admin typography page
$app->get('/admin/typo', function () use ($app) {

    /** @var Environment $twig */
    $twig = $app['twig'];

    return $twig->render('back/typo.twig', []);

})->bind('admin_typo');

// Admin singer page
$app->get('/admin/singer', function () use ($app) {

    /** @var Environment $twig */
    $twig = $app['twig'];

    return $twig->render('back/singer.twig', []);

})->bind('admin_singer');

// Admin vote page
$app->get('/admin/vote', function () use ($app) {

    /** @var Environment $twig */
    $twig = $app['twig'];

    return $twig->render('back/vote.twig', []);

})->bind('admin_singer_vote');

// Admin get config
$app->get('/ajax/config', function (Request $request) use ($app) {

    $query = $request->query->all();

    $config = [];

    /** @var Connection $db */
    $db = $app['db'];

    $type = $query['type'];

    $res = $db->executeQuery('SELECT * from backend_config', [
        $type
    ]);

    while ($row = $res->fetch()) {
        if (!empty($row['mime_type'])) {
            $row['value'] = 'data:' . $row['mime_type'] . ';base64, ' . base64_encode($row['value']);
        }
        $config[$row['name']] = $row['value'];
    }

    return $app->json($config);

})->bind('ajax_config_list');

// Admin set config
$app->post('/ajax/config', function (Request $request) use ($app) {

    $body = $request->request->all();

    /** @var Connection $db */
    $db = $app['db'];

    $type = $body['name'];
    foreach ($body as $key => $item) {

        $id = $db->fetchColumn('SELECT id from backend_config WHERE name = ? and type = ?', [
            $key,
            $type
        ], 0);

        if (!$id) {
            $db->insert('backend_config', [
                'value' => $item,
                'name' => $key,
                'type' => $type,
            ]);
        } else {
            $db->update('backend_config', [
                'value' => $item
            ], [
                'type' => $type,
                'name' => $key
            ]);
        }
    }

    /** @var UploadedFile $file */
    $files = $request->files->all();

    foreach ($files as $key => $file) {
        $id = $db->fetchColumn('SELECT id from backend_config WHERE name = ? and type = ? ', [
            $key,
            $type
        ], 0);

        if (!$id) {
            $data = file_get_contents($file->getPathname());
            $db->insert('backend_config', [
                'value' => $data,
                'name' => $key,
                'type' => $type,
                'mime_type' => $file->getMimeType()
            ]);
        } else {
            $data = file_get_contents($file->getPathname());
            $db->update('backend_config', [
                'value' => $data,
                'mime_type' => $file->getMimeType()
            ], [
                'type' => $type,
                'name' => $key
            ]);
        }
    }

    return $app->json($body);

})->bind('ajax_config_save');

// Admin get partners
$app->get('/ajax/partner', function (Request $request) use ($app) {

    $partners = [];

    /** @var Connection $db */
    $db = $app['db'];

    $res = $db->executeQuery('SELECT * from partners');

    while ($row = $res->fetch()) {

        if (!empty($row['mime_type'])) {
            $row['img64'] = 'data:' . $row['mime_type'] . ';base64, ' . base64_encode($row['img']);
            unset($row['img']);
        }
        $partners[] = $row;
    }

    return $app->json($partners);

    // Select configs with type media

})->bind('ajax_partner_list');

// Admin add partner
$app->post('/ajax/partner/add', function (Request $request) use ($app) {


    /** @var Connection $db */
    $db = $app['db'];

    $db->insert('partners', [
        'name' => null,
        'link' => null
    ]);

    return $app->json([
        'success' => true
    ]);

})->bind('ajax_partner_add');

// Admin delete partner
$app->post('/ajax/partner/delete', function (Request $request) use ($app) {

    $body = $request->request->all();

    /** @var Connection $db */
    $db = $app['db'];

    $db->delete('partners', [
        'id' => $body['id']
    ]);

    return $app->json([
        'success' => true
    ]);

})->bind('ajax_partner_delete');

// Admin set partners
$app->post('/ajax/partners/save', function (Request $request) use ($app) {

    $body = $request->request->all();
    $partners = [];
    /** @var Connection $db */
    $db = $app['db'];

    foreach ($body as $fieldName => $values) {

        foreach ($values as $partnerId => $value) {
            if (!array_key_exists($partnerId, $partners)) {
                $partners[$partnerId] = [];
            }
            $partners[$partnerId][$fieldName] = $value;
        }
    }

    $files = $request->files->all();

    foreach ($files as $fieldName => $values) {
        /** @var UploadedFile $value */
        foreach ($values as $partnerId => $value) {
            if (!array_key_exists($partnerId, $partners)) {
                $partners[$partnerId] = [];
            }
            $partners[$partnerId][$fieldName] = file_get_contents($value->getPathname());
            $partners[$partnerId]['mime_type'] = $value->getMimeType();
        }

    }

    foreach ($partners as $id => $partner) {
        $db->update('partners', $partner, [
            'id' => $id
        ]);
    }

    return $app->json(['success' => true]);

})->bind('ajax_partner_save');

// Admin get singer
$app->get('/ajax/singer', function (Request $request) use ($app) {

    $query = $request->query->all();

    $singers = [];

    /** @var Connection $db */
    $db = $app['db'];

    $type = $query['type'];

    $res = $db->executeQuery('SELECT * from `option`');

    while ($row = $res->fetch()) {

        if (!empty($row['mime_type'])) {
            $row['img64'] = 'data:' . $row['mime_type'] . ';base64, ' . base64_encode($row['img']);
            unset($row['img']);
        }
        if (!empty($row['music_link'])) {

            $currentDirName = dirname(__FILE__);
            $musicFile = $currentDirName . '/music/' . $row['music_link'];

            if (file_exists($musicFile)) {
                $row['music_link'] = $app['music_path'] . '/' . $row['music_link'];
            } else {
                $row['music_link'] = null;
            }
        }
        $singers[] = $row;
    }

    return $app->json($singers);

    // Select configs with type media

})->bind('ajax_singer_list');

// Admin set singer
$app->post('/ajax/singer', function (Request $request) use ($app) {

    $dbFields = [
        'name' => null,
        'surname' => null,
        'music_name' => null,
        'music_link' => null,
        'age' => null,
        'city' => null,
        'img' => null,
        'mime_type' => null
    ];

    /** @var Connection $db */
    $db = $app['db'];
    $body = $request->request->all();
    $affected = 0;

    $id = $db->fetchColumn('SELECT id from `option` WHERE id = ?', [
        $body['id']
    ], 0);

    if ($id) {
        if ($request->files->has('img')) {
            /** @var UploadedFile $imgFile */
            $imgFile = $request->files->get('img');
            $body['img'] = file_get_contents($imgFile->getPathname());
            $body['mime_type'] = $imgFile->getMimeType();
        }

        if ($request->files->has('music')) {
            /** @var UploadedFile $musicFile */
            $musicFile = $request->files->get('music');
            $musicFileTmpPath = $musicFile->getPathname();
            $currentDirName = dirname(__FILE__);
            $musicFileName = md5($musicFile->getFilename()) . '.' . $musicFile->getClientOriginalExtension();
            move_uploaded_file($musicFileTmpPath, $currentDirName . '/music/' . $musicFileName);
            $body['music_link'] = $musicFileName;

        }
        // var_dump($body);
        foreach ($dbFields as $key => $value) {
            if (array_key_exists($key, $body)) {
                $dbFields[$key] = $body[$key];
            } else {
                unset($dbFields[$key]);
            }
        }
        $affected = $db->update('`option`', $dbFields, [
            'id' => $id
        ]);
    }

    return $app->json([
        'success' => $affected
    ]);

    // Select configs with type media

})->bind('ajax_singer_update');

// Admin delete singer
$app->post('/ajax/singer/delete', function (Request $request) use ($app) {

    /** @var Connection $db */
    $db = $app['db'];
    $body = $request->request->all();

    $affected = $db->delete('`option`', [
        'id' => $body['id']
    ]);

    return $app->json([
        'success' => $affected
    ]);

})->bind('ajax_singer_delete');

// Admin add singer
$app->post('/ajax/singer/add', function (Request $request) use ($app) {

    $dbFields = [
        'name' => null,
        'surname' => null,
        'music_name' => null,
        'music_link' => null,
        'age' => null,
        'city' => null,
        'img' => null,
        'mime_type' => null
    ];

    /** @var Connection $db */
    $db = $app['db'];
    $body = $request->request->all();

    if ($request->files->has('img')) {
        /** @var UploadedFile $imgFile */
        $imgFile = $request->files->get('img');
        $body['img'] = file_get_contents($imgFile->getPathname());
        $body['mime_type'] = $imgFile->getMimeType();
    }

    if ($request->files->has('music')) {
        /** @var UploadedFile $musicFile */
        $musicFile = $request->files->get('music');
        $musicFileTmpPath = $musicFile->getPathname();
        $currentDirName = dirname(__FILE__);
        $musicFileName = md5($musicFile->getFilename()) . '.' . $musicFile->getClientOriginalExtension();
        move_uploaded_file($musicFileTmpPath, $currentDirName . '/music/' . $musicFileName);
        $body['music_link'] = $musicFileName;

    }

    foreach ($dbFields as $key => $value) {
        if (array_key_exists($key, $body)) {
            $dbFields[$key] = $body[$key];
        } else {
            unset($dbFields[$key]);
        }
    }

    $affected = $db->insert('`option`', $dbFields);
    $db->insert('vote', [
        'poll_id' => 1,
        'poll_option_id' => $db->lastInsertId(),
        'vote_count' => 0,
    ]);
    return $app->json([
        'success' => $affected
    ]);

})->bind('ajax_singer_add');

// Admin get votes
$app->get('/ajax/vote', function (Request $request) use ($app) {

    $votes = [];

    /** @var Connection $db */
    $db = $app['db'];

    $qb = $db->createQueryBuilder();
    $res = $qb->select('s.id', 's.name', 's.surname', 's.music_name', 'v.vote_count')
        ->from('`option`', 's')
        ->leftJoin('s', 'vote', 'v', 's.id = v.poll_option_id')
        ->execute();

    while ($row = $res->fetch()) {
        $votes[] = $row;
    }

    return $app->json($votes);

})->bind('ajax_vote_list');

// Admin get suspicious votes
$app->post('/ajax/suspicious/{page}', function (Request $request, $page) use ($app) {

    /** @var Connection $db */
    $db = $app['db'];
    $suspicious = [
        'records' => [],
        'current_page' => 0,
        'page_count' => 0
    ];
    $limit = 10;
    $offset = 0;
    $perPageCount = 2;
    $body = $request->request->all();
    $singer_id = $body['singer_id'];
    $time_limit = 180;
    $qb = $db->createQueryBuilder();

    if (intval($page) > 0) {
        $limit = intval($page) * $perPageCount;
        $offset = $limit - $perPageCount;
    }

    $qb->select('id', 'external_id', 'first_name', 'last_name',
        'auth_type', 'timestampdiff(SECOND,last_login,last_voted) as diff')
        ->from('ordinary_user')
        ->where(' vote_cancel = 0')
        ->andWhere('voted = 1')
        ->andWhere("singer_id = $singer_id")
        ->andWhere("timestampdiff(SECOND,last_login,last_voted) <= $time_limit");

    $rowCount = $db->fetchColumn('select count(a.id) from (' . $qb->getSQL() . ') a', [], 0);

    $pageCount = ceil($rowCount / $perPageCount);

    $res = $qb->setFirstResult($offset)
        ->setMaxResults($limit)
        ->execute();

    while ($row = $res->fetch()) {
        $suspicious['records'][] = $row;
    }

    $suspicious['page_count'] = $pageCount;
    $suspicious['current_page'] = intval($page);

    return $app->json($suspicious);

})->bind('ajax_suspicious_list');

// Admin cancel votes
$app->post('/ajax/vote/cancel', function (Request $request) use ($app) {

    $body = $request->request->all();

    /** @var Connection $db */
    $db = $app['db'];
    $canceled = 0;
    $res = $db->executeQuery('select id,external_id,first_name,last_name,auth_type,
            timestampdiff(SECOND,last_login,last_voted) as diff from ordinary_user where voted = 1 and vote_cancel = 0
            and singer_id = ? and timestampdiff(SECOND,last_login,last_voted) <= 180;', [$body['singer_id']]);

    while ($row = $res->fetch()) {
        $db->update('ordinary_user', [
            'vote_cancel' => 1
        ], [
            'id' => $row['id']
        ]);
        $canceled++;
    }

    $db->executeQuery('update vote set vote_count = vote_count - ? where poll_option_id = ? and vote_count >= ?', [
        $canceled,
        $body['singer_id'],
        $canceled
    ]);

    return $app->json(['message' => 'success']);

})->bind('ajax_cancel_vote');

// Admin add votes
$app->post('/ajax/vote/add', function (Request $request) use ($app) {

    $body = $request->request->all();

    /** @var Connection $db */
    $db = $app['db'];


    $db->executeQuery('update vote set vote_count = vote_count + ? where poll_option_id = ?', [
        $body['vote_count'],
        $body['singer_id']
    ]);

    return $app->json(['message' => 'success']);

})->bind('ajax_add_vote');

// Admin subtract votes
$app->post('/ajax/vote/subtract', function (Request $request) use ($app) {

    $body = $request->request->all();

    /** @var Connection $db */
    $db = $app['db'];


    $db->executeQuery('update vote set vote_count = vote_count - ? where poll_option_id = ? and vote_count >= ?', [
        $body['vote_count'],
        $body['singer_id'],
        $body['vote_count']
    ]);

    return $app->json(['message' => 'success']);

})->bind('ajax_subtract_vote');

// Admin update poll status
$app->post('/ajax/poll/update', function (Request $request) use ($app) {

    $body = $request->request->all();

    /** @var Connection $db */
    $db = $app['db'];

    $db->executeQuery('update poll set status = ? where id = 1', [
        $body['poll_status'],
    ]);

    return $app->json(['message' => 'success']);

})->bind('ajax_update_poll');

// Login page
$app->get('/login', function (Request $request) use ($app) {

    /** @var SessionInterface $session */
    $session = $app['session'];

    /** @var Environment $twig */
    $twig = $app['twig'];

    return $twig->render('back/login.twig', [
        'error' => $app['security.last_error']($request),
        'last_username' => $session->get('_security.last_username')
    ]);
})->bind('login');

// Main page
$app->get('/', function () use ($app) {

    /** @var Environment $twig */
    $twig = $app['twig'];

    /** @var Connection $db */
    $db = $app['db'];

    // dump($app['captcha.test']('f9871'));

    /** @var SessionInterface $session */
    $session = $app['session'];

    $singers = [];
    $votes = [];
    $config = [
        'media' => []
    ];
    $partners = [];
    $vote_total = 0;
    $poll_status = $db->fetchColumn('SELECT status FROM poll WHERE id = 1', [], 0);
    if ($poll_status == 0) {
        return $app->redirect('/result');
    } else {
        $singer_result = $db->executeQuery('select * from `option`');
        $vote_result = $db->executeQuery('select * from vote');
        $config_result = $db->executeQuery('select * from backend_config');
        $partner_result = $db->executeQuery('select * from partners');

        while ($row = $partner_result->fetch()) {
            if (!empty($row['mime_type'])) {
                $row['img'] = 'data:' . $row['mime_type'] . ';base64, ' . base64_encode($row['img']);
            }
            $partners[] = $row;
        }

        while ($row = $config_result->fetch()) {
            if (!empty($row['mime_type'])) {
                $row['value'] = 'data:' . $row['mime_type'] . ';base64, ' . base64_encode($row['value']);
            }
            $config[$row['type']][$row['name']] = $row['value'];
        }

        while ($vote = $vote_result->fetch()) {
            if ($vote['vote_count']) {
                $votes[$vote['poll_option_id']] = $vote['vote_count'];
                $vote_total += $vote['vote_count'];
            }
        }

        while ($singer = $singer_result->fetch()) {
            if (array_key_exists($singer['id'], $votes)) {
                $vote_count = $votes[$singer['id']];
                $singer['vote_count'] = round(($vote_count * 100) / $vote_total);
            } else {
                $singer['vote_count'] = 0;
            }
            $singer['img'] = 'data:' . $singer['mime_type'] . ';base64, ' . base64_encode($singer['img']);
            $singers[] = $singer;
        }

        return $twig->render('front/index.twig', array(
            'singers' => $singers,
            'config' => $config,
            'partners' => $partners,
            'user' => ($session->has('user')) ? $session->get('user') : false
        ));
    }
})->bind('main');

// Result page
$app->get('/result', function () use ($app) {
    /** @var Environment $twig */
    $twig = $app['twig'];

    /** @var Connection $db */
    $db = $app['db'];

    /** @var SessionInterface $session */
    $session = $app['session'];

    $vote_total = 0;
    $votes = [];
    $winner = null;
    $config = [
        'media' => []
    ];

    $poll_status = $db->fetchColumn('SELECT status FROM poll WHERE id = 1', [], 0);
    if ($poll_status == 1) {
        return $app->redirect('/');
    } else {
        $singer_result = $db->executeQuery('select * from `option`');
        $vote_result = $db->executeQuery('select * from vote');
        $config_result = $db->executeQuery('select * from backend_config');

        while ($row = $config_result->fetch()) {
            if (!empty($row['mime_type'])) {
                $row['value'] = 'data:' . $row['mime_type'] . ';base64, ' . base64_encode($row['value']);
            }
            $config[$row['type']][$row['name']] = $row['value'];
        }

        while ($vote = $vote_result->fetch()) {
            if ($vote['vote_count']) {
                $votes[$vote['poll_option_id']] = $vote['vote_count'];
                $vote_total += $vote['vote_count'];
            }
        }

        while ($singer = $singer_result->fetch()) {
            if (array_key_exists($singer['id'], $votes)) {
                $vote_count = $votes[$singer['id']];
                $singer['vote_count'] = round(($vote_count * 100) / $vote_total);
            } else {
                $singer['vote_count'] = 0;
            }
            $singer['img'] = 'data:' . $singer['mime_type'] . ';base64, ' . base64_encode($singer['img']);
            if ($winner == null || $winner['vote_count'] < $singer['vote_count']) {
                $winner = $singer;
            }
        }

        return $twig->render('front/result.twig', array(
            'config' => $config,
            'singers' => [$winner],
            'user' => ($session->has('user')) ? $session->get('user') : false
        ));
    }
})->bind('result');

// Get poll status
$app->get('/poll/status', function () use ($app) {
    /** @var Connection $db */
    $db = $app['db'];

    $poll_status = $db->fetchColumn('SELECT status FROM poll WHERE id = 1', [], 0);

    return $app->json(['status' => intval($poll_status)]);
})->bind('poll_status');

// Vote
$app->post('/vote', function (Request $request) use ($app) {
    $post = $request->request->all();

    /** @var SessionInterface $session */
    $session = $app['session'];
    $json = array(
        'code' => 0,
        'result' => null,
        'message' => ''
    );

    try {
        if (!$session->has('user')) {

            $json['code'] = HAS_NOT_PERMISSION_ERROR;
            return $app->json($json, 501);
        } else {

            /** @var User $user */
            $user = $session->get('user');

            if (!$user->canVote()) {

                $json['code'] = ALREADY_VOTED_ERROR;
                return $app->json($json, 501);
            } else {
                $votedUser = $user->vote($app['db'], $post['singer_id']);
                $session->set('user', $votedUser);
                $json['message'] = 'Голос принят';
            }
        }

        return $app->json($json, 200);
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
});

// Auth oauth
$app->get('/oauth/{name}', function (Request $request, $name) use ($app) {

    /** @var \Classes\Oauth $oauth */
    $oauth = $app['oauth'];
    /** @var SessionInterface $session */
    $session = $app['session'];
    if (!$oauth->has($name)) {
        throw new NotFoundHttpException("Social with key $name does not exist in oauth service");
    }

    $redirectUri = $oauth->generateAuthUrl($name);
    $session->set('auth_social', $name);
    return $app->redirect($redirectUri);

})->bind('oauth');

$app->run();