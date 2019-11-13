<?php

namespace FondOfSpryker\Yves\GoogleMicrodata;

use FondOfSpryker\Yves\GoogleMicrodata\Twig\GoogleMicrodataTwigExtension;
use Spryker\Shared\Kernel\Store;
use Spryker\Yves\Kernel\AbstractFactory;

class GoogleMicrodataFactory extends AbstractFactory
{
    /**
     * @return GoogleMicrodataTwigExtension
     */
    public function createGoogleMicrodataTwigExtension(): GoogleMicrodataTwigExtension
    {
        return new GoogleMicrodataTwigExtension(
            $this->getFeedBuilderPlugins()
        );
    }

    /**
     * @return \FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder\FeedBuilderInterface[]
     *
     * @throws
     */
    protected function getFeedBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleMicrodataDependencyProvider::PLUGINS_FEEDBUILDER);
    }

    /**
     * @return Store
     */
    public function getStore(): Store
    {
        return Store::getInstance();
    }

    /**
     * @return GoogleMicrodataConfig
     */
    public function getGoogleMicrodataConfig(): GoogleMicrodataConfig
    {
        return $this->getConfig();
    }
}
