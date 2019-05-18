<?php


namespace Classes;


use DateTime;
use Doctrine\DBAL\Connection;
use Exception;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class FrontendControllerProvider implements ControllerProviderInterface
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
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // Main page
        $controllers->get('/', function () use ($app) {

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
                        $percent = ($vote_count * 100) / $vote_total;
                        $singer['vote_count'] = round($percent, 2, PHP_ROUND_HALF_UP);
                        // $singer['vote_count'] = round(($vote_count * 100) / $vote_total);
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

        // Logout
        $controllers->get('/logout', function () use ($app) {
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

        // Result page
        $controllers->get('/result', function () use ($app) {
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
        $controllers->get('/poll/status', function () use ($app) {
            /** @var Connection $db */
            $db = $app['db'];

            $poll_status = $db->fetchColumn('SELECT status FROM poll WHERE id = 1', [], 0);

            return $app->json(['status' => intval($poll_status)]);
        })->bind('poll_status');

        // Vote
        $controllers->post('/vote', function (Request $request) use ($app) {
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

        // Oauth authorization
        $controllers->get('/oauth/{name}', function ($name) use ($app) {

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

        return $controllers;
    }
}