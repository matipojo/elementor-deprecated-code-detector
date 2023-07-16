# Elementor Deprecated Code Detector

This is a tool to detect deprecated code in Elementor ecosystem plugins.

Currently, it scans 100 top Elementor plugins from WordPress.org.

## Installation

```bash
git clone git@github.com:matipojo/elementor-deprecated-code-detector.git
cd deprecation-detector
```

## Usage

### Scan new deprecated code

Update the versions of `elementor` and `elementor-pro` in `composer.json` file to versions you want to scan.

(p.s. for scanning Elementor Pro you need to have a valid license key, see the [instructions](https://developers.elementor.com/docs/cli/composer/#install-elementor-pro/).

Then run:

```bash
composer update
composer run scan:source
```

### Scan deprecated code in plugins

For one plugin:
```bash
composer require wpackagist-plugin/<plugin-name>
```
For a specific version
```bash
composer require wpackagist-plugin/<plugin-name>:<version>
```

For a list of plugins, add them to the `composer.json` require section and run:
```bash
composer update
```

Then run the scan:
```bash
composer run scan:plugins
```
