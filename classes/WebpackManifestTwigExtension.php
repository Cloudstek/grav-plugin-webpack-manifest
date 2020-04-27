<?php

declare(strict_types=1);

namespace Grav\Plugin\WebpackManifest;

use Twig;

/**
 * Webpack Manifest Twig extension.
 */
class WebpackManifestTwigExtension extends Twig\Extension\AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function getGlobals()
    {
        return [
            'manifest' => new WebpackManifestAssets()
        ];
    }
}
