<?php

namespace FondOfSpryker\Yves\GoogleMicrodata\Twig;

use FondOfSpryker\Shared\GoogleMicrodata\GoogleMicrodataConstants;
use Spryker\Shared\Twig\TwigExtension;
use Twig_Environment;
use Twig_SimpleFunction;

class GoogleMicrodataTwigExtension extends TwigExtension
{
    public const FUNCTION_GOOGLE_MICRODATA = 'googleMicrodata';

    /**
     * @var \FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder\FeedBuilderInterface[]
     */
    protected $feedBuilderPlugins;

    /**
     * @param \FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder\FeedBuilderInterface[] $feedBuilderPlugins
     */
    public function __construct(array $feedBuilderPlugins)
    {
        $this->feedBuilderPlugins = $feedBuilderPlugins;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            $this->createMicrodataFunction(),
        ];
    }

    /**
     * @return \Twig_SimpleFunction
     */
    protected function createMicrodataFunction(): Twig_SimpleFunction
    {
        return new Twig_SimpleFunction(
            static::FUNCTION_GOOGLE_MICRODATA,
            [$this, 'renderMicroData'],
            [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]
        );
    }

    /**
     * @param \Twig_Environment $twig
     * @param $page
     * @param $params
     *
     * @return string
     */
    public function renderMicroData(Twig_Environment $twig, $page, $params): string
    {
        switch ($page) {
            case GoogleMicrodataConstants::PAGE_TYPE_PRODUCT:
                /** @var \FondOfSpryker\Yves\GoogleMicrodata\Plugin\FeedBuilder\ProductFeedBuilderPlugin $productFeedBuilder */
                $productFeedBuilder = $this->feedBuilderPlugins[GoogleMicrodataConstants::PAGE_TYPE_PRODUCT];
                $feedData = $productFeedBuilder->getFeed($params);

                break;
        }

        return $twig->render(
            $this->getMicrodataTemplateName(),
            [
                'feed' => $feedData,
            ]
        );
    }

    /**
     * @return string
     */
    protected function getMicrodataTemplateName(): string
    {
        return '@GoogleMicrodata/partials/microdata.twig';
    }
}
