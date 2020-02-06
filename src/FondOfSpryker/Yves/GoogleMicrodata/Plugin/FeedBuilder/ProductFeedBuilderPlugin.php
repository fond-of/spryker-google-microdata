<?php

namespace FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder;

use DateTime;
use Exception;
use FondOfSpryker\Shared\GoogleMicrodata\GoogleMicrodataConstants;
use Generated\Shared\Transfer\GoogleMicrodataBrandTransfer;
use Generated\Shared\Transfer\GoogleMicrodataOffersTransfer;
use Generated\Shared\Transfer\GoogleMicrodataTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Yves\Kernel\AbstractPlugin;

/**
 * @method \FondOfSpryker\Yves\GoogleMicrodata\GoogleMicrodataFactory getFactory()
 */
class ProductFeedBuilderPlugin extends AbstractPlugin implements FeedBuilderInterface
{
    use LoggerTrait;

    public const CONTEXT = '@context';
    public const TYPE = '@type';
    public const TYPE_OFFER = 'Offer';
    public const TYPE_THING = 'Thing';
    public const SCHEMA_IN_STOCK = 'http://schema.org/InStock';
    public const SCHEMA_OUT_OF_STOCK = 'http://schema.org/OutOfStock';

    public const PRODUCT_ATTRIBUTE_IS_SOLD_OUT = 'is_sold_out';
    public const PRODUCT_ATTRIBUTE_SPECIAL_PRICE = 'special_price';
    public const PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM = 'special_price_from';
    public const PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO = 'special_price_to';

    /**
     * @return string
     */
    public function getName(): string
    {
        return GoogleMicrodataConstants::PAGE_TYPE_PRODUCT;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getFeed(array $params): string
    {
        if (!array_key_exists(GoogleMicrodataConstants::PAGE_TYPE_PRODUCT, $params)) {
            return '';
        }

        return json_encode($this->handle($params), \JSON_PRETTY_PRINT);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function handle(array $params): array
    {
        try {
        /** @var \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer */
            $productViewTransfer = $params[GoogleMicrodataConstants::PAGE_TYPE_PRODUCT];

            $googleMicrodataTransfer = new GoogleMicrodataTransfer();
            $googleMicrodataTransfer->setName($productViewTransfer->getName());
            $googleMicrodataTransfer->setDescription($productViewTransfer->getDescription() ?: $productViewTransfer->getMetaDescription());
            $googleMicrodataTransfer->setSku($productViewTransfer->getSku());

        /** @var \Generated\Shared\Transfer\ProductImageStorageTransfer $productImageStorageTransfer */
            if (array_key_exists('image', $params)) {
                $productImageStorageTransfer = $params['image'];
                $googleMicrodataTransfer->setImage($productImageStorageTransfer->getExternalUrlLarge());
            }

            $googleMicrodataTransfer->setOffers($this->getOffers($productViewTransfer));
            $googleMicrodataTransfer->setBrand($this->getBrand());

            return array_merge(
                [static::CONTEXT => 'http://schema.org', static::TYPE => ucfirst($this->getName())],
                $googleMicrodataTransfer->toArray(true, true)
            );
        } catch (Exception $exception) {
            $this->getLogger()->error($exception->getMessage(), $exception->getTrace());
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return array
     */
    protected function getBrand(): array
    {
        $store = $this->getFactory()->getStore();
        $storeNameArray = explode("_", $store->getStoreName());

        $googleMicrodataBrandTransfer = new GoogleMicrodataBrandTransfer();
        $googleMicrodataBrandTransfer->setName(ucfirst(strtolower($storeNameArray[0])));

        return array_merge([static::TYPE => static::TYPE_THING], $googleMicrodataBrandTransfer->toArray(true, true));
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return array
     */
    protected function getOffers(ProductViewTransfer $productViewTransfer): array
    {
        $googleMicrodataOffersTransfer = new GoogleMicrodataOffersTransfer();
        $googleMicrodataOffersTransfer->setPrice(round($this->getPrice($productViewTransfer) / 100, 2));
        $googleMicrodataOffersTransfer->setPriceCurrency($this->getFactory()->getStore()->getCurrencyIsoCode());
        $googleMicrodataOffersTransfer->setUrl($this->getFactory()->getGoogleMicrodataConfig()->getYvesHost() . '/' . $productViewTransfer->getUrl());
        $googleMicrodataOffersTransfer->setAvailability($this->getAvailability($productViewTransfer));

        return array_merge(
            [static::TYPE => static::TYPE_OFFER],
            $googleMicrodataOffersTransfer->toArray(true, true)
        );
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return string
     */
    protected function getAvailability(ProductViewTransfer $productViewTransfer): string
    {
        if (array_key_exists(static::PRODUCT_ATTRIBUTE_IS_SOLD_OUT, $productViewTransfer->getAttributes())
            && $productViewTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_IS_SOLD_OUT] === 'yes'
        ) {
            return static::SCHEMA_OUT_OF_STOCK;
        }

        if ($productViewTransfer->getAvailable() === false) {
            return static::SCHEMA_OUT_OF_STOCK;
        }

        return static::SCHEMA_IN_STOCK;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return float
     */
    protected function getPrice(ProductViewTransfer $productViewTransfer): float
    {
        if (!array_key_exists(static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE, $productViewTransfer->getAttributes())
            || !array_key_exists(static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM, $productViewTransfer->getAttributes())
            || !$productViewTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE]
            || !$productViewTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM]
        ) {
            return $productViewTransfer->getPrice();
        }

        $current = new DateTime();
        $from = new DateTime($productViewTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM]);
        $to = array_key_exists(static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO, $productViewTransfer->getAttributes()) &&
            $productViewTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO]
                ? new DateTime($productViewTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO])
                : null;

        if (($from <= $current && $to === null) || ($from <= $current && $to >= $current)) {
            return $productViewTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE];
        }

        return $productViewTransfer->getPrice();
    }
}
