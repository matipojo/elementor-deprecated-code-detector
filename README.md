# Elementor Deprecated Code Detector

This is a tool to detect deprecated code in Elementor ecosystem plugins.

## Prerequisites

- PHP >= 8.0
- Composer >= 2.0

## Installation

```bash
git clone git@github.com:matipojo/elementor-deprecated-code-detector.git
cd elementor-deprecated-code-detector
```

## Usage

### Scan new deprecated code

Update the versions of `elementor` and `elementor-pro` in `composer.json` file to versions you want to scan.

(p.s. for scanning Elementor Pro you need to have a valid license key, see the [instructions](https://developers.elementor.com/docs/cli/composer/#install-elementor-pro/).)

Then run:

```bash
composer update
composer run scan:source
```

### Scan deprecated code in plugins from WordPress.org

For one plugin:
```bash
composer require wpackagist-plugin/<plugin-slug>
```
For a specific version
```bash
composer require wpackagist-plugin/<plugin-slug>:<version>
```

For a list of plugins, add them to the `composer.json` require section and run:
```bash
composer update
composer run scan:plugins
```

For scanning top 100 plugins from WordPress.org, run:
```bash
composer run require:top100
composer run scan:plugins
```

### Scan deprecated code in a custom plugin
If the plugin is available via Composer, add it to the `composer.json` require section and run:

```bash
composer require <package-name>
composer run scan:plugins
```

Otherwise, put the plugin in the `wp-content/plugins` folder and run:
```bash
composer run scan:plugins
```
