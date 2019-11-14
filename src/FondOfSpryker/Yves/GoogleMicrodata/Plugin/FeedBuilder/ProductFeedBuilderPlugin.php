<?php

namespace FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder;

use FondOfSpryker\Shared\GoogleMicrodata\GoogleMicrodataConstants;
use Generated\Shared\Transfer\GoogleMicrodataBrandTransfer;
use Generated\Shared\Transfer\GoogleMicrodataOffersTransfer;
use Generated\Shared\Transfer\GoogleMicrodataTransfer;
use Generated\Shared\Transfer\ProductImageStorageTransfer;
use Generated\Shared\Transfer\StorageProductImageTransfer;
use Generated\Shared\Transfer\StorageProductTransfer;
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
        /** @var StorageProductTransfer $storageProductTransfer */
        $storageProductTransfer = $params[GoogleMicrodataConstants::PAGE_TYPE_PRODUCT];

        $googleMicrodataTransfer = new GoogleMicrodataTransfer();
        $googleMicrodataTransfer->setName($storageProductTransfer->getName());
        $googleMicrodataTransfer->setDescription($storageProductTransfer->getDescription() ?: $storageProductTransfer->getMetaDescription());
        $googleMicrodataTransfer->setSku($storageProductTransfer->getSku());

        /** @var StorageProductImageTransfer $storageProductImageTransfer */
        if (array_key_exists('image', $params) && $params['image'] instanceof StorageProductImageTransfer) {
            $storageProductImageTransfer = $params['image'];
            $googleMicrodataTransfer->setImage($storageProductImageTransfer->getExternalUrlLarge());
        }

        $googleMicrodataTransfer->setOffers($this->getOffers($storageProductTransfer));
        $googleMicrodataTransfer->setBrand($this->getBrand());

        return array_merge(
            [static::CONTEXT => 'http://schema.org', static::TYPE => ucfirst($this->getName())],
            $googleMicrodataTransfer->toArray(true, true)
        );
    }

    /**
     * @param StorageProductTransfer $storageProductTransfer
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
     * @param  StorageProductTransfer $storageProductTransfer
     * @return array
     */
    protected function getOffers(StorageProductTransfer $storageProductTransfer): array
    {
        $googleMicrodataOffersTransfer = new GoogleMicrodataOffersTransfer();
        $googleMicrodataOffersTransfer->setPrice(round($this->getPrice($storageProductTransfer)/100, 2));
        $googleMicrodataOffersTransfer->setPriceCurrency($this->getFactory()->getStore()->getCurrencyIsoCode());
        $googleMicrodataOffersTransfer->setUrl($this->getFactory()->getGoogleMicrodataConfig()->getYvesHost() . '/' . $storageProductTransfer->getUrl());
        $googleMicrodataOffersTransfer->setAvailability($this->getAvailability($storageProductTransfer));

        return array_merge(
            [static::TYPE => static::TYPE_OFFER],
            $googleMicrodataOffersTransfer->toArray(true, true)
        );
    }

    /**
     * @param StorageProductTransfer $storageProductTransfer
     *
     * @return string
     */
    protected function getAvailability(StorageProductTransfer $storageProductTransfer): string
    {
        if (array_key_exists(static::PRODUCT_ATTRIBUTE_IS_SOLD_OUT, $storageProductTransfer->getAttributes())
            && $storageProductTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_IS_SOLD_OUT] === 'yes'
        ) {
            return static::SCHEMA_OUT_OF_STOCK;
        }

        if ($storageProductTransfer->getAvailable() === false) {
            return static::SCHEMA_OUT_OF_STOCK;
        }

        return static::SCHEMA_IN_STOCK;
    }

    /**
     * @param StorageProductTransfer $storageProductTransfer
     *
     * @return float
     *
     * @throws
     */
    protected function getPrice(StorageProductTransfer $storageProductTransfer): float
    {
        if (!array_key_exists(static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE, $storageProductTransfer->getAttributes())
            || !array_key_exists(static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM, $storageProductTransfer->getAttributes())
            || !$storageProductTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE]
            || !$storageProductTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM]
        ) {
            return $storageProductTransfer->getPrice();
        }

        $current = new \DateTime();
        $from = new \DateTime($storageProductTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM]);
        $to = array_key_exists(static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO, $storageProductTransfer->getAttributes()) &&
            $storageProductTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO]
                ? new \DateTime($storageProductTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO])
                : null;

        if (($from <= $current && $to === null) || ($from <= $current && $to >= $current)) {
            return $storageProductTransfer->getAttributes()[static::PRODUCT_ATTRIBUTE_SPECIAL_PRICE];
        }

        return $storageProductTransfer->getPrice();
    }
}
