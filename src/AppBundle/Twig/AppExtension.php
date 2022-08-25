<?php

namespace AppBundle\Twig;

use AppBundle\Enums\MacroSplit;
use AppBundle\Enums\MealType;
use AppBundle\Enums\PlanType;
use AppBundle\Enums\Language;
use AppBundle\Enums\CookingTime;
use IvoPetkov\VideoEmbed;
use NumberFormatter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Node\Node;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;

class AppExtension extends AbstractExtension
{
    private UrlGeneratorInterface $router;
    private string $s3beforeAfterImages;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $s3beforeAfterImages)
    {
        $this->router = $urlGenerator;
        $this->s3beforeAfterImages = $s3beforeAfterImages;
    }

    public function getFilters()
    {
        return [
            new TwigFilter("isYoutubeVideo", [$this, "isYoutubeVideo"]),
            new TwigFilter("getYouTubeVideoKey", [$this, "getYouTubeVideoKey"]),
            new TwigFilter("isVimeoVideo", [$this, "isVimeoVideo"]),
            new TwigFilter("getVimeoKey", [$this, "getVimeoKey"]),
            new TwigFilter("content", [$this, "getContent"]),
            new TwigFilter('picture_filter', [$this, 'picture_filter']),
            new TwigFilter('embed_video', [$this, 'embed_video']),
            new TwigFilter('isDateTimeObject', [$this, 'isDateTimeObject']),
            new TwigFilter('meal_type', [$this, 'mealType']),
            new TwigFilter('macro_split', [$this, 'macroSplit']),
            new TwigFilter('cooking_time', [$this, 'cookingTime']),
            new TwigFilter('currency_symbol', [$this, 'currencySymbol']),
            new TwigFilter('with_tax', [$this, 'withTax']),
            new TwigFilter('tax', [$this, 'tax']),
            new TwigFilter('number_symbol', [$this, 'numberSymbol']),
            new TwigFilter('locale_language', [$this, 'localeLanguage']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('meal_types', [$this, 'mealTypes']),
            new TwigFunction('macro_splits', [$this, 'macroSplits']),
            new TwigFunction('locales', [$this, 'locales']),
            new TwigFunction('plan_types', [$this, 'planTypes']),
            new TwigFunction('cooking_times', [$this, 'cookingTimes']),
            new TwigFunction('path_if', [$this, 'getPathIf'], array('is_safe_callback' => [$this, 'isUrlGenerationSafe'])),
        ];
    }

    /**
     * @param $currency
     * @param string $locale
     *
     * @return string|null
     */
    public function currencySymbol($currency)
    {
        $formatter = new NumberFormatter("en-US@currency=$currency", NumberFormatter::CURRENCY);
        return $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    }

    public function withTax($value, $taxRate = 0)
    {
        if ($taxRate > 0) {
            $tax = $value*$taxRate/100;
            return $value+$tax;
        }
        return $value;
    }

    public function tax($value, $taxRate = 0)
    {
        if ($taxRate > 0) {
            return round($value * $taxRate/100, 2);
        }
        return $value;
    }

    public function numberSymbol($value)
    {
        if ($value > 0) {
            return "+" . round($value, 1);
        }
        return round($value, 1);
    }

    public function isDateTimeObject($date)
    {
        return ($date instanceof \DateTime);
    }

    public function getYouTubeVideoKey($videoUrl)
    {
        if (str_contains($videoUrl, '/shorts/')) {
            preg_match('/.+\/shorts\/([^"&?\/ ]{11})/', $videoUrl, $m);
        } else {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $videoUrl, $m);
        }

        return $m[1];
    }

    public function getVimeoKey($videoUrl)
    {
        preg_match('/^.*(?:vimeo.com)\/(?:channels\/|channels\/\w+\/|groups\/[^\/]*\/videos\/|album‌​\/\d+\/video\/|video\/|)(\d+)(?:$|\/|\?)/', $videoUrl, $m);
        return $m[1];
    }


    public function isVimeoVideo($videoUrl)
    {
        $count = preg_match('/^.*(?:vimeo.com)\/(?:channels\/|channels\/\w+\/|groups\/[^\/]*\/videos\/|album‌​\/\d+\/video\/|video\/|)(\d+)(?:$|\/|\?)/', $videoUrl);

        return ($count > 0);
    }

    public function isYoutubeVideo(string $videoUrl): bool
    {
        return str_contains($videoUrl, 'yout');
    }

    public function getName(): string
    {
        return 'app_extension';
    }

    public function getContent($path)
    {
        return file_get_contents($path);
    }

    /**
     * @param string $picture
     * @param string $type
     * @param * $size
     * @return string
     */
    public function picture_filter($picture, $type = null, $size = null)
    {
        if (!$picture || preg_match("/^https?/i", $picture)) {
            return $picture;
        }

        $source = $this->s3beforeAfterImages;

        if ($type) {
            $source .= $type . '/';
        }

        if ($size) {
            $source .= $size . '/';
        }

        return $source . $picture;
    }

    /**
     * @param string $url
     * @param int $width
     * @param int $height
     * @return string
     */
    public function embed_video($url, $width = 480, $height = 270)
    {
        $video = new VideoEmbed($url);
        $video->setSize($width, $height);

        return $video->html;
    }

    /**
     * @return array
     */
    public function planTypes()
    {
        return PlanType::readables();
    }

    /**
     * @return array
     */
    public function mealTypes()
    {
        return MealType::readables();
    }

    /**
     * @param int $type
     * @return string
     */
    public function mealType($value)
    {
        return MealType::accepts($value) ? MealType::readableFor($value) : 'None';
    }

    /**
     * @return array
     */
    public function cookingTimes()
    {
        return CookingTime::readables();
    }

    /**
     * @param int $type
     * @return string
     */
    public function cookingTime($value)
    {
        return CookingTime::accepts($value) ? CookingTime::readableFor($value) : 'None';
    }

    /**
     * @param int $value
     * @return string
     */
    public function localeLanguage($value)
    {
        switch ($value) {
            case 'en': {
                return 'English';
            }
            case 'da_DK': {
                return 'Danish';
            }
            case 'sv_SE': {
                return 'Swedish';
            }
            case 'nb_NO': {
                return 'Norwegian';
            }
            case 'nl_NL': {
                return 'Dutch';
            }
            case 'fi_FI': {
                return 'Finnish';
            }
            case 'de_DE': {
                return 'German';
            }
            default: {
                return 'Other';
            }
        }
    }

    public function locales()
    {
        return Language::readables();
    }

    /**
     * @return array
     */
    public function macroSplits()
    {
        return MacroSplit::readables();
    }

    /**
     * @param int $value
     * @return string
     */
    public function macroSplit($value)
    {
        return MacroSplit::accepts($value) ? MacroSplit::readableFor($value) : 'None';
    }

    /**
     * @param bool $boolean
     * @param string $name
     * @param array $parameters
     * @param bool $relative
     * @param string $default
     *
     * @return string
     */
    public function getPathIf($boolean, $name, $parameters = array(), $relative = false, $default = '#')
    {
        return $boolean ? $this->router->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH) : $default;
    }



    /**
     * Determines at compile time whether the generated URL will be safe and thus
     * saving the unneeded automatic escaping for performance reasons.
     *
     * The URL generation process percent encodes non-alphanumeric characters. So there is no risk
     * that malicious/invalid characters are part of the URL. The only character within an URL that
     * must be escaped in html is the ampersand ("&") which separates query params. So we cannot mark
     * the URL generation as always safe, but only when we are sure there won't be multiple query
     * params. This is the case when there are none or only one constant parameter given.
     * E.g. we know beforehand this will be safe:
     * - path('route')
     * - path('route', {'param': 'value'})
     * But the following may not:
     * - path('route', var)
     * - path('route', {'param': ['val1', 'val2'] }) // a sub-array
     * - path('route', {'param1': 'value1', 'param2': 'value2'})
     * If param1 and param2 reference placeholder in the route, it would still be safe. But we don't know.
     *
     * @param Node $argsNode The arguments of the path/url function
     *
     * @return array An array with the contexts the URL is safe
     *
     * @final since version 3.4, type-hint to be changed to "\Twig\Node\Node" in 4.0
     */
    public function isUrlGenerationSafe(Node $argsNode)
    {
        // support named arguments
        $paramsNode = $argsNode->hasNode('parameters') ? $argsNode->getNode('parameters') : (
        $argsNode->hasNode(1) ? $argsNode->getNode(1) : null
        );

        if (null === $paramsNode || $paramsNode instanceof ArrayExpression && \count($paramsNode) <= 2 &&
            (!$paramsNode->hasNode(1) || $paramsNode->getNode(1) instanceof ConstantExpression)
        ) {
            return array('html');
        }

        return array();
    }

}
