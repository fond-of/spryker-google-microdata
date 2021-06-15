<?php

namespace FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder;

use DateTime;
use Exception;
use FondOfSpryker\Shared\GoogleMicrodata\GoogleMicrodataConstants;
use Generated\Shared\Transfer\GoogleMicrodataBrandTransfer;
use Generated\Shared\Transfer\GoogleMicrodataOffersTransfer;
use Generated\Shared\Transfer\GoogleMicrodataTransfer;
use Generated\Shared\Transfer\ProductImageStorageTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Yves\Kernel\AbstractPlugin;
use Spryker\Yves\Money\Plugin\MoneyPlugin;

/**
 * @method \FondOfSpryker\Yves\GoogleMicrodata\GoogleMicrodataFactory getFactory()
 */
class ProductFeedBuilderPlugin extends AbstractPlugin implements FeedBuilderInterface
{
    use LoggerTrait;

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

            $googleMicrodataTransfer = (new GoogleMicrodataTransfer())
                ->setName($productViewTransfer->getName())
                ->setDescription($productViewTransfer->getDescription() ?: $productViewTransfer->getMetaDescription())
                ->setSku($productViewTransfer->getSku())
                ->setOffers($this->getOffers($productViewTransfer))
                ->setBrand($this->getBrand());

            if (array_key_exists('image', $params) && $params['image'] instanceof ProductImageStorageTransfer) {
                $productImageStorageTransfer = $params['image'];
                $googleMicrodataTransfer->setImage($productImageStorageTransfer->getExternalUrlLarge());
            }

            return array_merge([
                GoogleMicrodataConstants::CONTEXT => 'http://schema.org',
                GoogleMicrodataConstants::TYPE => ucfirst($this->getName()),
                ], $googleMicrodataTransfer->toArray(true, true));
        } catch (Exception $exception) {
            $this->getLogger()->error($exception->getMessage(), $exception->getTrace());
        }

        return [];
    }

    /**
     * @return array
     */
    protected function getBrand(): array
    {
        $storeName = $this->getFactory()
            ->getStore()
            ->getStoreName();

        $storeNameArray = explode('_', $storeName);

        $googleMicrodataBrandTransfer = (new GoogleMicrodataBrandTransfer())
            ->setName(ucfirst(strtolower($storeNameArray[0])));

        return array_merge([GoogleMicrodataConstants::TYPE => GoogleMicrodataConstants::TYPE_THING], $googleMicrodataBrandTransfer->toArray(true, true));
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return array
     */
    protected function getOffers(ProductViewTransfer $productViewTransfer): array
    {
        $googleMicrodataOffersTransfer = (new GoogleMicrodataOffersTransfer())
            ->setPrice($this->getFactory()->getMoneyPlugin()->convertIntegerToDecimal($productViewTransfer->getPrice()))
            ->setPriceCurrency($this->getFactory()->getStore()->getCurrencyIsoCode())
            ->setSalePrice($this->getSalePrice($productViewTransfer))
            ->setUrl($this->getFactory()->getConfig()->getYvesHost() . '/' . $productViewTransfer->getUrl())
            ->setAvailability($this->getAvailability($productViewTransfer));

        return array_merge(
            [GoogleMicrodataConstants::TYPE => GoogleMicrodataConstants::TYPE_OFFER],
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
        if (
            array_key_exists(GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_IS_SOLD_OUT, $productViewTransfer->getAttributes())
            && $productViewTransfer->getAttributes()[GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_IS_SOLD_OUT] === 'yes'
        ) {
            return GoogleMicrodataConstants::SCHEMA_OUT_OF_STOCK;
        }

        if ($productViewTransfer->getAvailable() === false) {
            return GoogleMicrodataConstants::SCHEMA_OUT_OF_STOCK;
        }

        return GoogleMicrodataConstants::SCHEMA_IN_STOCK;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return float|null
     */
    protected function getSalePrice(ProductViewTransfer $productViewTransfer): ?float
    {
        if (
            !array_key_exists(GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE, $productViewTransfer->getAttributes())
            || !array_key_exists(GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM, $productViewTransfer->getAttributes())
            || !$productViewTransfer->getAttributes()[GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE]
            || !$productViewTransfer->getAttributes()[GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM]
        ) {
            return null;
        }

        try {
            $current = new DateTime();
            $from = new DateTime($productViewTransfer->getAttributes()[GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM]);
            $to = array_key_exists(GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO, $productViewTransfer->getAttributes()) &&
            $productViewTransfer->getAttributes()[GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO]
                ? new DateTime($productViewTransfer->getAttributes()[GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO])
                : null;

            if (($from <= $current && $to === null) || ($from <= $current && $to >= $current)) {
                return $this->getFactory()
                    ->getMoneyPlugin()
                    ->convertIntegerToDecimal($productViewTransfer->getAttributes()[GoogleMicrodataConstants::PRODUCT_ATTRIBUTE_SPECIAL_PRICE]);
            }
        } catch (Exception $exception) {
            $this->getLogger()->error($exception->getMessage(), $exception->getTrace());
        }

        return null;
    }
}
