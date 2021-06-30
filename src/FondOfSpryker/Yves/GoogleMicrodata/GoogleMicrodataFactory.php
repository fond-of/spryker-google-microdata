<?php

namespace FondOfSpryker\Yves\GoogleMicrodata;

use FondOfSpryker\Yves\GoogleMicrodata\Twig\GoogleMicrodataTwigExtension;
use Spryker\Shared\Kernel\Store;
use Spryker\Shared\Money\Dependency\Plugin\MoneyPluginInterface;
use Spryker\Yves\Kernel\AbstractFactory;

/**
 * @method \FondOfSpryker\Yves\GoogleMicrodata\GoogleMicrodataConfig getConfig()
 */
class GoogleMicrodataFactory extends AbstractFactory
{
    /**
     * @return \FondOfSpryker\Yves\GoogleMicrodata\Twig\GoogleMicrodataTwigExtension
     */
    public function createGoogleMicrodataTwigExtension(): GoogleMicrodataTwigExtension
    {
        return new GoogleMicrodataTwigExtension(
            $this->getFeedBuilderPlugins()
        );
    }

    /**
     * @return \FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder\FeedBuilderInterface[]
     */
    protected function getFeedBuilderPlugins(): array
    {
        return $this->getProvidedDependency(GoogleMicrodataDependencyProvider::PLUGINS_FEEDBUILDER);
    }

    /**
     * @return \Spryker\Shared\Kernel\Store
     */
    public function getStore(): Store
    {
        return $this->getProvidedDependency(GoogleMicrodataDependencyProvider::STORE);
    }

    /**
     * @return \Spryker\Shared\Kernel\Store
     */
    public function getMoneyPlugin(): MoneyPluginInterface
    {
        return $this->getProvidedDependency(GoogleMicrodataDependencyProvider::PLUGIN_MONEY);
    }
}
