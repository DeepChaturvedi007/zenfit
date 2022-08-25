<?php declare(strict_types=1);

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

class WebpackExtension extends AbstractExtension
{
    /** @var array<mixed> */
    private array $manifest = [];
    private int $modified = 0;
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /** @return array<TwigFilter> */
    public function getFilters(): array
    {
        return [
            new TwigFilter('webpack_asset', [$this, 'webpackAsset']),
        ];
    }

    public function webpackAsset(string $name): string
    {
        if ($this->manifest === []) {
            $manifestFile = $this->rootDir . '/web/js/dist/manifest.json';
            $jsonContents   = file_get_contents($manifestFile);
            $this->manifest = json_decode($jsonContents, true, 512, JSON_THROW_ON_ERROR);
            $this->modified = (int) filemtime($manifestFile);
        }

        return "/js/dist/" . $this->manifest[$name] . "?" . $this->modified;
    }

    /** @return array<TwigFunction> */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('meal_assets', [$this, 'mealAssets']),
        ];
    }

    public function getName(): string
    {
        return "app_webpack_extension";
    }

    /** @return array<mixed> */
    public function mealAssets(): array
    {
        static $manifest = null;

        if (null === $manifest) {
            $file = $this->rootDir . '/web/js/meals/asset-manifest.json';
            $manifest = collect(json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR));
        }

        $find = static function($pattern) use ($manifest) {
            return $manifest->first(function ($value, $key) use ($pattern) {
                return preg_match("/^" . $pattern . "(\..+)?.js$/i", $key);
            });
        };

        return [
            $find('runtime~main'),
            $find('static\/js\/3'),
            $find('main'),
        ];
    }
}
