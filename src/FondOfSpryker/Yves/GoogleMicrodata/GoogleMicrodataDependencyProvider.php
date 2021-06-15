<?php

namespace FondOfSpryker\Yves\GoogleMicrodata;

use FondOfSpryker\Shared\GoogleMicrodata\GoogleMicrodataConstants;
use FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder\ProductFeedBuilderPlugin;
use Spryker\Shared\Kernel\Store;
use Spryker\Yves\Kernel\AbstractBundleDependencyProvider;
use Spryker\Yves\Kernel\Container;
use Spryker\Yves\Money\Plugin\MoneyPlugin;

class GoogleMicrodataDependencyProvider extends AbstractBundleDependencyProvider
{
    public const PLUGINS_FEEDBUILDER = 'PLUGINS_FEEDBUILDER';
    public const PLUGIN_MONEY = 'PLUGIN_MONEY';
    public const STORE = 'STORE';

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    public function provideDependencies(Container $container): Container
    {
        $container = $this->addFeedBuilderPlugins($container);
        $container = $this->addStore($container);
        $container = $this->addMoneyPlugin($container);

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addFeedBuilderPlugins(Container $container): Container
    {
        $self = $this;

        $container[static::PLUGINS_FEEDBUILDER] = static function () use ($self) {
            return $self->getFeedBuilderPlugins();
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addStore(Container $container): Container
    {
        $container[static::STORE] = Store::getInstance();

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return Container
     */
    protected function addMoneyPlugin(Container $container): Container
    {
        $container[static::PLUGIN_MONEY] = new MoneyPlugin();

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
