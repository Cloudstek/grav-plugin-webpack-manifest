<?php

namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Common\Twig\Twig;
use Grav\Plugin\WebpackManifest\WebpackManifestTwigExtension;

/**
 * Webpack Manifest plugin class.
 *
 * @author Maarten de Boer <maarten@cloudstek.nl>
 * @license MIT
 */
class WebpackManifestPlugin extends Plugin
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => [
                ['onPluginsInitialized', 0],
                ['autoload', 100000]
            ]
        ];
    }

    /**
     * Composer autoload.
     *
     * @return ClassLoader
     */
    public function autoload()
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the plugin.
     */
    public function onPluginsInitialized()
    {
        if ($this->isAdmin() || $this->config["plugins.{$this->name}.enabled"] !== true) {
            return;
        }

        $this->enable(
            [
                'onTwigExtensions' => ['onTwigExtensions', 0]
            ]
        );
    }

    /**
     * Initialise twig extension.
     */
    public function onTwigExtensions()
    {
        $this->grav['twig']->twig->addExtension(new WebpackManifestTwigExtension());
    }
}
