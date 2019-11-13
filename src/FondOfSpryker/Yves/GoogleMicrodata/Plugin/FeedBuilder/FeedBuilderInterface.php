<?php

namespace FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder;

interface FeedBuilderInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param array $params
     *
     * @return string
     */
    public function getFeed(array $params): string;
}
