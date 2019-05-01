<?php
/**
 * Created by PhpStorm.
 * Oauth: xterminate
 * Date: 10.03.2019
 * Time: 13:57
 */

namespace Classes;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
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
                $social = $serializer->denormalize($item, $className);
                $socials[$key] = $social;

            };

            return new Oauth($socials);
        };
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        // $dispatcher->addListener(KernelEvents::REQUEST, function (FilterResponseEvent $event) use ($app) {
        //     // do something
        // });
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