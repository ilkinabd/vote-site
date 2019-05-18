<?php
/**
 * Created by PhpStorm.
 * Oauth: xterminate
 * Date: 10.03.2019
 * Time: 13:57
 */

namespace Classes;


use DateTime;
use Doctrine\DBAL\Connection;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Serializer;

class OauthServiceProvider implements ServiceProviderInterface, BootableProviderInterface, EventListenerProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $app
     */
    public function register(Container $app)
    {

        $app['oauth'] = function ($app) {
            $cfgSocials = $app['oauth.config.socials'];

            /** @var Serializer $serializer */
            $serializer = $app['serializer'];
            $socials = [];
            foreach ($cfgSocials as $key => $item) {

                $className = "Classes\\" . strtoupper($key) . "Social";

                if (!class_exists($className)) {
                    throw new UnprocessableEntityHttpException("$className does not exist");
                };
                $item['name'] = $key;
                /** @var Social $social */
                $social = $serializer->denormalize($item, $className);
                $social->setRedirectUri($app['oauth.config.redirect_uri']);
                $socials[$key] = $social;

            };

            return new Oauth($socials);
        };
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(KernelEvents::REQUEST, function (GetResponseEvent $event) use ($app) {

            $request = $event->getRequest();

            $get = $request->query->all();

            /** @var SessionInterface $session */
            $session = $app['session'];

            if (isset($get['code']) && $session->has('auth_social') && !$session->has('user')) {

                /** @var Oauth $oauth */
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

        });
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     *
     * @param Application $app
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}