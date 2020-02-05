<?php

namespace FondOfSpryker\Yves\GoogleMicrodata;

use FondOfSpryker\Shared\GoogleMicrodata\GoogleMicrodataConstants;
use Spryker\Yves\Kernel\AbstractBundleConfig;

class GoogleMicrodataConfig extends AbstractBundleConfig
{
    /**
     * @return string
     */
    public function getYvesHost(): string
    {
        return $this->get(GoogleMicrodataConstants::BASE_URL_YVES);
    }
}
