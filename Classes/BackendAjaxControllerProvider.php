<?php


namespace Classes;


use Doctrine\DBAL\Connection;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class BackendAjaxControllerProvider implements ControllerProviderInterface
{

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        // Creates a new controller based on the default route
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        // Admin set config
        $controllers->post('/config', function (Request $request) use ($app) {

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
                $mimeType = $file->getMimeType();
                $fileData = file_get_contents($file->getPathname());

                if ($mimeType === 'image/svg+') {
                    $mimeType .= 'xml';
                }

                if (!$id) {
                    $db->insert('backend_config', [
                        'value' => $fileData,
                        'name' => $key,
                        'type' => $type,
                        'mime_type' => $mimeType
                    ]);
                } else {
                    $db->update('backend_config', [
                        'value' => $fileData,
                        'mime_type' => $mimeType
                    ], [
                        'type' => $type,
                        'name' => $key
                    ]);
                }
            }

            return $app->json($body);

        })->bind('ajax_config_save');

        // Admin get config
        $controllers->get('/config', function (Request $request) use ($app) {

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
                    if ($row['mime_type'] === 'image/svg+') {
                        $row['mime_type'] .= 'xml';
                    }
                    $row['value'] = 'data:' . $row['mime_type'] . ';base64, ' . base64_encode($row['value']);
                }
                $config[$row['name']] = $row['value'];
            }

            return $app->json($config);

        })->bind('ajax_config_list');

        // Admin get partners
        $controllers->get('/partner', function () use ($app) {

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
        $controllers->post('/partner/add', function () use ($app) {


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
        $controllers->post('/partner/delete', function (Request $request) use ($app) {

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
        $controllers->post('/partners/save', function (Request $request) use ($app) {

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
        $controllers->get('/singer', function (Request $request) use ($app) {

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
                    $musicFile = $currentDirName . '/../web/music/' . $row['music_link'];

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
        $controllers->post('/singer', function (Request $request) use ($app) {

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
                    move_uploaded_file($musicFileTmpPath, $currentDirName . '/../web/music/' . $musicFileName);
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
        $controllers->post('/singer/delete', function (Request $request) use ($app) {

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
        $controllers->post('/singer/add', function (Request $request) use ($app) {

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
        $controllers->get('/vote', function () use ($app) {

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
        $controllers->post('/suspicious/{page}', function (Request $request, $page) use ($app) {

            /** @var Connection $db */
            $db = $app['db'];
            $suspicious = [];
            $limit = null;
            $offset = null;
            $perPageCount = 10;
            $body = $request->request->all();
            $singer_id = $body['singer_id'];
            $time_limit = 180;
            $qb = $db->createQueryBuilder();

            if (intval($page) > 0) {
                $limit = $perPageCount;
                $offset = (intval($page) - 1) * $perPageCount;
            }

            $qb->select('id', 'external_id', 'first_name', 'last_name',
                'auth_type', 'timestampdiff(SECOND,last_login,last_voted) as diff')
                ->from('ordinary_user')
                ->where('vote_cancel = 0')
                ->andWhere('voted = 1')
                ->andWhere("singer_id = $singer_id")
                ->andWhere("timestampdiff(SECOND,last_login,last_voted) <= $time_limit");

            $rowCount = $db->fetchColumn('select count(a.id) from (' . $qb->getSQL() . ') a', [], 0);

            $pageCount = ceil($rowCount / $perPageCount);

            if ($offset) {
                $qb->setFirstResult(intval($offset));
            }

            if ($limit) {
                $qb->setMaxResults(intval($limit));
            }

            $res = $qb->execute();
            $suspicious['records'] = [];
            $suspicious['count'] = $rowCount;
            while ($row = $res->fetch()) {
                $suspicious['records'][] = $row;
            }

            $suspicious['page_count'] = $pageCount;
            $suspicious['offset'] = $offset;
            $suspicious['limit'] = $limit;
            $suspicious['current_page'] = intval($page);

            return $app->json($suspicious);

        })->bind('ajax_suspicious_list');

        // Admin cancel votes
        $controllers->post('/vote/cancel', function (Request $request) use ($app) {

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
        $controllers->post('/vote/add', function (Request $request) use ($app) {

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
        $controllers->post('/vote/subtract', function (Request $request) use ($app) {

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
        $controllers->post('/poll/update', function (Request $request) use ($app) {

            $body = $request->request->all();

            /** @var Connection $db */
            $db = $app['db'];

            $db->executeQuery('update poll set status = ? where id = 1', [
                $body['poll_status'],
            ]);

            return $app->json(['message' => 'success']);

        })->bind('ajax_update_poll');

        return $controllers;
    }
}