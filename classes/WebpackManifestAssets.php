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
     * @var Boolean
     */
    private $isDevelopment;

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

        // Default manifestPath
        $manifestPath = 'theme://manifest.json';
        // Load path from config
        $manifestPathConfigPath = $this->config['filepath'];

        // Determine manifest file path.
        if (empty($this->config['filepath']) === false) {
            // Set is Development variable
            $this->isDevelopment = $this->isWebpackDevServerRunning();
            // Grab webpack manifest.json based on development status
            $manifestPath = $this->isDevelopment ? $this->getWebpackDevServerURL() : 'theme://' . $manifestPathConfigPath;
            // Setup build folder
            list($buildFolder) = preg_split("/\/(?!.*\/)/", $manifestPathConfigPath);
            $this->buildFolder = $this->isDevelopment !== true ? 'theme://' . $buildFolder : $buildFolder;
        }

        // Ignore this check if we are in development mode
        if ($this->isDevelopment !== true) $manifestPath = $this->locator->findResource($manifestPath);

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
     * Get webpack dev server url for manifest file
     *
     * @return null|string $url
     */
    public function getWebpackDevServerURL()
    {
        $manifestPathConfigPath = $this->config['filepath'];
        // Check if these variable is set
        if (
            empty($this->config['devServer']['enabled']) === false &&
            empty($this->config['devServer']['port']) === false &&
            empty($this->config['devServer']['server']) === false
        ) {
            // Get host, port and protocol from config file
            $host = $this->config['devServer']['host'];
            $port = $this->config['devServer']['port'];
            $server = $this->config['devServer']['server'];
            // Build url to manifest file
            return "$server://$host:$port/$manifestPathConfigPath";
        } else {
            return null;
        }
    }

    /**
     * Check manifest file to see if it is available via webpack dev server
     *
     * @return Boolean true | false
     */
    public function isWebpackDevServerRunning()
    {
        $url = $this->getWebpackDevServerURL();
        if (!$url) return false;
        $headers = @get_headers($url, 1);
        // detect if development mode is on
        return $headers && strpos($headers[0], "200");
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
     * Modify path for a CSS or JS asset based on development or production.
     *
     * @param array|string $asset
     *
     * @return string $assetName
     */
    public function handleAssetPath($asset)
    {
         // Check if requested assets is public assets or not
         $isPublicAssets = strpos($asset, "assets");

         // Get the name of the asset requested
         if (isset($this->isDevelopment) && $this->isDevelopment && $isPublicAssets !== false) {
             $assetName = substr($asset, $isPublicAssets);
         } else {
             $assetName = substr(strrchr($asset, '/'), 1);
         }
 
         // Switch to the js version for HRM development
         if (isset($this->isDevelopment) && $this->isDevelopment && !isset($this->manifest[$assetName])) {
             $assetName = str_replace("css", "js", $assetName);
         }

         return $assetName;
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
        $isRequested = $this->locator->isStream($asset);

        // Get the correspond asset based on production or development build
        $assetName = $this->handleAssetPath($asset);

        // Replace non-hash path with manifest path
        if ($isRequested && isset($this->manifest[$assetName])) {
            $args[0] = $this->buildFolder . $this->manifest[$assetName];
        }

        // Use js version for HRM development
        if (isset($this->isDevelopment) && $this->isDevelopment) call_user_func_array([$this->assets, 'addJs'], $args);

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
        $isRequested = $this->locator->isStream($asset);

        // Get the correspond asset based on production or development build
        $assetName = $this->handleAssetPath($asset);

        // Replace non-hash path with manifest path
        if ($isRequested && isset($this->manifest[$assetName])) {
            $args[0] = $this->buildFolder . $this->manifest[$assetName];
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
