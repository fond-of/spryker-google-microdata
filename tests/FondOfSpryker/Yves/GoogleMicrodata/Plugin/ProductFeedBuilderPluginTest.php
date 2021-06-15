<?php

namespace FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder;

use Codeception\Test\Unit;
use FondOfSpryker\Shared\GoogleMicrodata\GoogleMicrodataConstants;
use FondOfSpryker\Yves\GoogleMicrodata\GoogleMicrodataConfig;
use FondOfSpryker\Yves\GoogleMicrodata\GoogleMicrodataFactory;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Shared\Kernel\Store;

class ProductFeedBuilderPluginTest extends Unit
{
    /**
     * @var GoogleMicrodataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $factoryMock;

    /**
     * @var ProductViewTransfer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productViewTransferMock;

    /**
     * @var Store|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    /**
     * @var GoogleMicrodataConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var \FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder\FeedBuilderInterface
     */
    protected $plugin;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->factoryMock = $this->getMockBuilder(GoogleMicrodataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productViewTransferMock = $this->getMockBuilder(ProductViewTransfer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(GoogleMicrodataConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new ProductFeedBuilderPlugin();
        $this->plugin->setFactory($this->factoryMock);
        $this->plugin->setConfig($this->configMock);
    }

    /**
     * @return void
     */
    public function testGetName(): void
    {
        static::assertEquals(GoogleMicrodataConstants::PAGE_TYPE_PRODUCT, $this->plugin->getName());
    }

    /**
     * @return void
     */
    public function testHandle(): void
    {
        $attributes = [
            GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE => '',
            GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM => '',
            GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO => '',
            GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_IS_SOLD_OUT => ''
        ];

        $this->factoryMock->expects(static::atLeastOnce())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->factoryMock->expects(static::atLeastOnce())
            ->method('getConfig')
            ->willReturn($this->configMock);

        $this->productViewTransferMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn('PRODUCT_NAME');

        $this->productViewTransferMock->expects(static::atLeastOnce())
            ->method('getDescription')
            ->willReturn('DESCRIPTION');

        $this->productViewTransferMock->expects(static::atLeastOnce())
            ->method('getSku')
            ->willReturn('SKU-000-000');

        $this->productViewTransferMock->expects(static::atLeastOnce())
            ->method('getSku')
            ->willReturn('SKU-000-000');

        $this->productViewTransferMock->expects(static::atLeastOnce())
            ->method('getPrice')
            ->willReturn(3990);

        $this->productViewTransferMock->expects(static::atLeastOnce())
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->productViewTransferMock->expects(static::atLeastOnce())
            ->method('getAvailable')
            ->willReturn(true);

        $this->storeMock->expects(static::atLeastOnce())
            ->method('getCurrencyIsoCode')
            ->willReturn('EUR');

        $this->storeMock->expects(static::atLeastOnce())
            ->method('getYvesHost')
            ->willReturn('https://shop.url');

        $this->plugin->getFeed([GoogleMicrodataConstants::PAGE_TYPE_PRODUCT => $this->productViewTransferMock]);
    }
}
