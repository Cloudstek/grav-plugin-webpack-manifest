<?php

declare(strict_types=1);

namespace Grav\Plugin\WebpackManifest;

use Grav\Common\Assets;
use Grav\Common\Config;
use Grav\Common\Grav;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

/**
 * Webpack Manifest Assets class.
 */
class WebpackManifestAssets
{
    /**
     * Assets.
     *
     * @var Assets
     */
    private $assets;

    /**
     * Plugin config.
     *
     * @var Config\Config
     */
    private $config;

    /**
     * Locator.
     *
     * @var UniformResourceLocator
     */
    private $locator;

    /**
     * Webpack manifest..
     *
     * @var array|null
     */
    private $manifest;

    public function __construct()
    {
        $grav = Grav::instance();
        $this->assets = $grav['assets'];
        $this->locator = $grav['locator'];
        $this->config = $grav['config']['plugins.webpack-manifest'];

        // Determine manifest file path.
        $manifestPath = 'theme://manifest.json';

        if (empty($this->config['filepath']) === false) {
            $manifestPath = 'theme://' . $this->config['filepath'];
        }

        $manifestPath = $this->locator->findResource($manifestPath);

        // Load manifest
        if ($manifestPath !== false) {
            $this->manifest = json_decode(file_get_contents($manifestPath), true);

            if ($this->manifest === null) {
                throw new \Exception(
                    sprintf(
                        'Could not decode webpack manifest file at %s: %s',
                        $manifestPath,
                        json_last_error_msg()
                    )
                );
            }
        }
    }

    /**
     * Add an asset or a collection of assets.
     *
     * It automatically detects the asset type (JavaScript, CSS or collection).
     * You may add more than one asset passing an array as argument.
     *
     * @param array|string $asset
     *
     * @return $this
     */
    public function add($asset)
    {
        // Skip processing if no manifest file could be found.
        if ($this->manifest === null) {
            $this->assets->add($asset);

            return $this;
        }

        // Function arguments.
        $args = func_get_args();

        // Handle recursion for multiple assets.
        $handledMultiple = $this->handleMultipleAssets($asset, [$this, 'add'], $args);

        if ($handledMultiple === true) {
            return $this;
        }

        // Add asset.
        $extension = pathinfo(parse_url($asset, PHP_URL_PATH), PATHINFO_EXTENSION);

        if (empty($extension) === false) {
            switch (strtolower($extension)) {
                case 'css':
                    call_user_func_array([$this, 'addCss'], $args);
                    break;
                case 'js':
                    call_user_func_array([$this, 'addJs'], $args);
                    break;
            }
        }

        return $this;
    }

    /**
     * Add a CSS asset or a collection of assets.
     *
     * @param array|string $asset
     *
     * @see Assets::addCss()
     *
     * @return $this
     */
    public function addCss($asset)
    {
        // Skip processing if no manifest file could be found.
        if ($this->manifest === null) {
            $this->assets->addCss($asset);

            return $this;
        }

        // Function arguments.
        $args = func_get_args();

        // Handle recursion for multiple assets.
        $handledMultiple = $this->handleMultipleAssets($asset, [$this, 'addCss'], $args);

        if ($handledMultiple === true) {
            return $this;
        }

        // Handle single asset.
        $assetPath = $this->locator->findResource($asset, false);

        // Replace the original file path with the located file path from our manifest
        if ($assetPath !== false && isset($this->manifest[$assetPath])) {
            $args[0] = $this->manifest[$assetPath];
        }

        call_user_func_array([$this->assets, 'addCss'], $args);

        return $this;
    }

    /**
     * Add a JS asset or a collection of assets.
     *
     * @param array|string $asset
     *
     * @see Assets::addJs()
     *
     * @return $this
     */
    public function addJs($asset)
    {
        // Skip processing if no manifest file could be found.
        if ($this->manifest === null) {
            $this->assets->addJs($asset);

            return $this;
        }

        // Function arguments.
        $args = func_get_args();

        // Handle recursion for multiple assets.
        $handledMultiple = $this->handleMultipleAssets($asset, [$this, 'addJs'], $args);

        if ($handledMultiple === true) {
            return $this;
        }

        // Handle single asset.
        $assetPath = $this->locator->findResource($asset, false);

        // Replace the original file path with the located file path from our manifest
        if ($assetPath !== false && isset($this->manifest[$assetPath])) {
            $args[0] = $this->manifest[$assetPath];
        }

        call_user_func_array([$this->assets, 'addJs'], $args);

        return $this;
    }

    /**
     * Handle recursion for multiple assets.
     *
     * @param string|array $assets
     * @param callable     $cb
     * @param array        $args
     *
     * @return bool True on multiple assets, false on single asset.
     */
    private function handleMultipleAssets($assets, $cb, $args)
    {
        // Handle multiple assets or a colleciton of assets
        if (is_array($assets)) {
            foreach ($assets as $asset) {
                array_shift($args);
                $args = array_merge([$asset], $args);
                call_user_func_array($cb, $args);
            }

            return true;
        } elseif (isset($this->assets->getCollections()[$assets])) {
            array_shift($args);
            $args = array_merge([$this->assets->getCollections()[$assets]], $args);
            call_user_func_array($cb, $args);

            return true;
        }

        return false;
    }
}
