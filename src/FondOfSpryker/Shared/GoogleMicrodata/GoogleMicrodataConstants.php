<?php

namespace FondOfSpryker\Shared\GoogleMicrodata;

interface GoogleMicrodataConstants
{
    /* config */
    public const BASE_URL_YVES = 'BASE_URL_YVES';

    public const PAGE_TYPE_PRODUCT = 'product';

    public const CONTEXT = '@context';
    public const TYPE = '@type';
    public const TYPE_OFFER = 'Offer';
    public const TYPE_THING = 'Thing';
    public const SCHEMA_IN_STOCK = 'http://schema.org/InStock';
    public const SCHEMA_OUT_OF_STOCK = 'http://schema.org/OutOfStock';
    public const PRODUCT_PRICE = 'price';
    public const PRODUCT_SALE_PRICE = 'sale_price';
    public const PRODUCT_CURRENCY = 'priceCurrency';
    public const PRODUCT_URL = 'url';
    public const PRODUCT_AVAILABILITY = 'availability';
    public const PRODUCT_ATTRIBUTE_IS_SOLD_OUT = 'is_sold_out';
    public const PRODUCT_ATTRIBUTE_SPECIAL_PRICE = 'special_price';
    public const PRODUCT_ATTRIBUTE_SPECIAL_PRICE_FROM = 'special_price_from';
    public const PRODUCT_ATTRIBUTE_SPECIAL_PRICE_TO = 'special_price_to';
}
