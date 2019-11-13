<?php

namespace FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder;

use FondOfSpryker\Shared\GoogleMicrodata\GoogleMicrodataConstants;
use Generated\Shared\Transfer\GoogleMicrodataBrandTransfer;
use Generated\Shared\Transfer\GoogleMicrodataOffersTransfer;
use Generated\Shared\Transfer\GoogleMicrodataTransfer;
use Generated\Shared\Transfer\ProductImageStorageTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;

/**
 * @method \FondOfSpryker\Yves\GoogleMicrodata\GoogleMicrodataFactory getFactory()
 */
class ProductFeedBuilderPlugin extends AbstractPlugin implements FeedBuilderInterface
{
    public const CONTEXT = '@context';
    public const TYPE = '@type';
    public const TYPE_OFFER = 'Offer';
    public const TYPE_THING = 'Thing';

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
        return json_encode($this->handle($params), \JSON_PRETTY_PRINT);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function handle(array $params): array
    {
        /** @var ProductViewTransfer $productViewTransfer */
        $productViewTransfer = $params[GoogleMicrodataConstants::PAGE_TYPE_PRODUCT];

        $googleMicrodataTransfer = new GoogleMicrodataTransfer();
        $googleMicrodataTransfer->setName($productViewTransfer->getName());
        $googleMicrodataTransfer->setDescription($productViewTransfer->getDescription() ?: $productViewTransfer->getMetaDescription());
        $googleMicrodataTransfer->setSku($productViewTransfer->getSku());

        /** @var ProductImageStorageTransfer $productImageStorageTransfer */
        $productImageStorageTransfer = $params['image'];
        $googleMicrodataTransfer->setImage($productImageStorageTransfer->getExternalUrlLarge());

        $googleMicrodataTransfer->setOffers($this->getOffers($productViewTransfer));
        $googleMicrodataTransfer->setBrand($this->getBrand());

        return array_merge(
            [static::CONTEXT => 'http://schema.org', static::TYPE => ucfirst($this->getName())],
            $googleMicrodataTransfer->toArray(true, true)
        );
    }

    /**
     * @param ProductViewTransfer $productViewTransfer
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
     * @param  ProductViewTransfer $productViewTransfer
     * @return array
     */
    protected function getOffers(ProductViewTransfer $productViewTransfer): array
    {
        $googleMicrodataOffersTransfer = new GoogleMicrodataOffersTransfer();
        $googleMicrodataOffersTransfer->setPrice(round($this->getPrice($productViewTransfer)/100, 2));
        $googleMicrodataOffersTransfer->setPriceCurrency($this->getFactory()->getStore()->getCurrencyIsoCode());
        $googleMicrodataOffersTransfer->setUrl($this->getFactory()->getGoogleMicrodataConfig()->getYvesHost() . '/' . $productViewTransfer->getUrl());
        $googleMicrodataOffersTransfer->setAvailability($productViewTransfer->getAvailable() ? 'http://schema.org/InStock' : 'http://schema.org/OutStock');

        return array_merge([static::TYPE => static::TYPE_OFFER], $googleMicrodataOffersTransfer->toArray(true, true));
    }

    /**
     * @param ProductViewTransfer $productViewTransfer
     *
     * @return float
     *
     * @throws
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

        $current = new \DateTime();
        $from = new \DateTime($productViewTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM]);
        $to = array_key_exists(static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO, $productViewTransfer->getAttributes()) &&
            $productViewTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO]
                ? new \DateTime($productViewTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO])
                : null;

        if (($from <= $current && $to === null) || ($from <= $current && $to >= $current)) {
            return $productViewTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE];
        }

        return $productViewTransfer->getPrice();
    }
}
