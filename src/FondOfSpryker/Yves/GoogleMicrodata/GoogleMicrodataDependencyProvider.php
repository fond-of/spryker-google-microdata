<?php

namespace FondOfSpryker\Yves\GoogleMicrodata;

use FondOfSpryker\Shared\GoogleMicrodata\GoogleMicrodataConstants;
use FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder\ProductFeedBuilderPlugin;
use Spryker\Yves\Kernel\AbstractBundleDependencyProvider;
use Spryker\Yves\Kernel\Container;

class GoogleMicrodataDependencyProvider extends AbstractBundleDependencyProvider
{
    public const PLUGINS_FEEDBUILDER = 'PLUGINS_FEEDBUILDER';

    /**
     * @param Container $container
     *
     * @return Container
     */
    public function provideDependencies(Container $container): Container
    {
        $container = $this->addFeedBuilderPlugins($container);

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addFeedBuilderPlugins(Container $container): Container
    {
        $container[static::PLUGINS_FEEDBUILDER] = function () {
            return $this->getFeedBuilderPlugins();
        };

        return $container;
    }

    /**
     * @return \FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder\FeedBuilderInterface[]
     */
    protected function getFeedBuilderPlugins(): array
    {
        return [
            GoogleMicrodataConstants::PAGE_TYPE_PRODUCT => new ProductFeedBuilderPlugin(),
        ];
    }
}
