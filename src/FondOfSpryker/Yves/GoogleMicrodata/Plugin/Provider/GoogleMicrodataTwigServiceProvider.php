<?php

namespace FondOfSpryker\Yves\GoogleMicrodata\Plugin\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Spryker\Yves\Kernel\AbstractPlugin;
use Twig_Environment;

/**
 * @method \FondOfSpryker\Yves\GoogleMicrodata\GoogleMicrodataFactory getFactory()
 */
class GoogleMicrodataTwigServiceProvider extends AbstractPlugin implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param \Silex\Application $app
     */
    public function register(Application $app)
    {
        $twigExtension = $this
            ->getFactory()
            ->createGoogleMicrodataTwigExtension();

        $app['twig'] = $app->share(
            $app->extend(
                'twig',
                function (Twig_Environment $twig) use ($twigExtension) {
                    $twig->addExtension($twigExtension);

                    return $twig;
                }
            )
        );
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     *
     * @return void
     */
    public function boot(Application $app)
    {
    }
}
