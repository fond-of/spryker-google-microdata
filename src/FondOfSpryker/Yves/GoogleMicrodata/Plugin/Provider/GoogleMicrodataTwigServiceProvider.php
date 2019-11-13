<?php

namespace FondOfSpryker\Yves\GoogleMicrodata\Plugin\Provider;

use Silex\ServiceProviderInterface;
use Spryker\Yves\Kernel\AbstractPlugin;
use Silex\Application;

/**
 * @package FondOfSpryker\Yves\GoogleMicrodata\Plugin\Provider
 * @method  \FondOfSpryker\Yves\GoogleMicrodata\GoogleMicrodataFactory getFactory()
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
                function (\Twig_Environment $twig) use ($twigExtension) {
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
     */
    public function boot(Application $app)
    {
    }
}
