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

The tool includes two composer commands:
- `scan:source` Scan the source code of Elementor and Elementor Pro and generates a list of deprecated code.
- `scan:plugins` Scan 3rd party plugins and detects if they use deprecated code.

The plugins code is scanned from `wp-content/plugins` folder.

The list of deprecated code is saved in `reports` folder.

### Scan new deprecated code

Update the version of `elementor` in `composer.json` file to version you want to scan.

For scanning deprecations in Elementor Pro, add it via composer, follow the [instructions](https://developers.elementor.com/docs/cli/composer/#install-elementor-pro/).

Then run:

```bash
composer update
composer scan:source
```

### Scan only Elementor or Elementor Pro
The scan commands will scan by default both Elementor and Elementor Pro (if added).

For scanning only one of them, add the plugin name to the command, for example:
```bash
composer scan:source:elementor
composer scan:plugins:elementor-pro
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
composer scan:source
composer scan:plugins
```

For scanning top 100 plugins from WordPress.org, run:
```bash
composer require:top100
composer scan:source
composer scan:plugins
```

### Scan deprecated code in a custom plugin
If the plugin is available via Composer, add it to the `composer.json` require section and run:

```bash
composer require <package-name>
composer scan:plugins
```

Otherwise, put the plugin in the `wp-content/plugins` folder and run:
```bash
composer scan:plugins
```
