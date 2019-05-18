<?php


namespace Classes;


use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class BackendControllerProvider implements ControllerProviderInterface
{

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return void A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // Admin login page
        $controllers->get('/login', function (Request $request) use ($app) {

            /** @var SessionInterface $session */
            $session = $app['session'];

            /** @var Environment $twig */
            $twig = $app['twig'];

            return $twig->render('back/login.twig', [
                'error' => $app['security.last_error']($request),
                'last_username' => $session->get('_security.last_username')
            ]);
        })->bind('admin_login');

        // Admin index page
        $controllers->get('/', function () use ($app) {

            return $app->redirect('/admin/typo');
        })->bind('admin_index');

        // Admin typography page
        $controllers->get('/typo', function () use ($app) {

            /** @var Environment $twig */
            $twig = $app['twig'];

            return $twig->render('back/typo.twig', []);

        })->bind('admin_typo');

        // Admin singer page
        $controllers->get('/singer', function () use ($app) {

            /** @var Environment $twig */
            $twig = $app['twig'];

            return $twig->render('back/singer.twig', []);

        })->bind('admin_singer');

        // Admin vote page
        $controllers->get('/vote', function () use ($app) {

            /** @var Environment $twig */
            $twig = $app['twig'];

            return $twig->render('back/vote.twig', []);

        })->bind('admin_vote');

        // Admin user page
        $controllers->get('/user', function () use ($app) {

            /** @var Environment $twig */
            $twig = $app['twig'];

            return $twig->render('back/user.twig', []);

        })->bind('admin_user');

        return $controllers;
    }
}