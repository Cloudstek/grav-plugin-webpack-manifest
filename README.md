# Webpack Manifest Plugin

The **Webpack Manifest** Plugin is an extension for [Grav CMS](http://github.com/getgrav/grav). It allows you to develop themes that use for example Webpack that generate versioned assets (css/js). 

As these generated files have a unique name every time they are generated, you can't reference them in your template directly. Webpack outputs a manifest file which contains a mapping between the unversioned filename and versioned filename, 
which is used by this plugin to replace assets with the correct file.

## Installation

Installing the Webpack Manifest plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install webpack-manifest

This will install the Webpack Manifest plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/webpack-manifest`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `webpack-manifest`. You can find these files on [GitHub](https://github.com/cloudstek/grav-plugin-webpack-manifest) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/webpack-manifest

> NOTE: This plugin is a modular component for Grav which may require other plugins to operate, please see its [blueprints.yaml-file on GitHub](https://github.com/cloudstek/grav-plugin-webpack-manifest/blob/master/blueprints.yaml).

### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/webpack-manifest/webpack-manifest.yaml` to `user/config/plugins/webpack-manifest.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
# Enable/disable the plugin
enabled: true
# Path to manifest.json relative to your active theme
# For webpack use manifest.json. For Laravel Mix use mix-manifest.json.
filepath: manifest.json
```

Note that if you use the Admin Plugin, a file with your configuration named webpack-manifest.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.

## Usage

Usage is simple as in most cases you can directly replace `{% do asset.css( ...` with `{% do manifest.css( ...`, the API is mostly the same and serves as a wrapper around some of the asset manager functionality, see https://learn.getgrav.org/16/themes/asset-manager.

Supported: **add()**, **addCss()**, **addJs()**