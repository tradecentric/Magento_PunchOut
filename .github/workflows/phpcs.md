# Magento2 PHPCS (`phpcs.yml`)

This workflow runs **PHP_CodeSniffer against the Magento2 coding standard** across a matrix
of all supported PHP 8.x versions. It is intended exclusively for **Magento 2 module repositories**.

---

## What this workflow does

1. Checkout repository
2. Setup PHP for each version in the matrix (`8.1`, `8.2`, `8.3`, `8.4`, `8.5`)
3. Validate `composer.json`
4. Restore per-PHP-version Composer cache
5. Run `composer update` to resolve dependencies compatible with the current matrix PHP version
6. Run PHPCS via `composer cs`

> **Note:** `composer update` is used instead of `composer install` so that each PHP version in the
> matrix independently resolves compatible package versions, rather than relying on a `composer.lock`
> generated on a single PHP version.

---

## Requirements

Each consuming repository must have the following in place before adding this workflow.

### `composer.json`

```json
{
    "require-dev": {
        "dealerdirect/phpcodesniffer-composer-installer": "^1.0",
        "magento/magento-coding-standard": "*",
        "squizlabs/php_codesniffer": "^3.9"
    },
    "config": {
        "allow-plugins": {
            "dealerdirect/phpcodesniffer-composer-installer": true
        }
    },
    "scripts": {
        "cs": "php -d auto_prepend_file=phpcs-bootstrap.php vendor/bin/phpcs --standard=phpcs.xml.dist",
        "cs:fixable": "php -d auto_prepend_file=phpcs-bootstrap.php vendor/bin/phpcbf --standard=phpcs.xml.dist"
    }
}
```

### `phpcs.xml.dist`

Defines the ruleset and scanned directories. Must include `warning-severity="0"` so only errors
block the pipeline, and the correct `installed_paths` for the Magento2 standard.

```xml
<?xml version="1.0"?>
<ruleset name="Your Module Name">
    <arg name="basepath" value="."/>
    <arg name="colors"/>
    <arg value="sp"/>
    <arg name="warning-severity" value="0"/>

    <config name="installed_paths" value="vendor/magento/magento-coding-standard,vendor/phpcsstandards/phpcsutils,vendor/magento/php-compatibility-fork"/>

    <file>Api</file>
    <file>Block</file>
    <file>Controller</file>
    <!-- add/remove directories to match your module structure -->

    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/view/*</exclude-pattern>

    <rule ref="Magento2"/>
</ruleset>
```

### `phpcs-bootstrap.php`

Required at the repository root. Stubs `Magento\Framework\Component\ComponentRegistrar` so PHPCS
can boot outside a full Magento installation without the autoloader blowing up.

---

## Usage

Copy `.github/workflows/phpcs.yml` into the repository. No other configuration is needed in the workflow file itself.

---

## Matrix

The workflow runs across all supported PHP 8.x versions on every PR to `main`.

| PHP version | Status |
|---|---|
| `8.1` | Security support |
| `8.2` | Active support |
| `8.3` | Active support |
| `8.4` | Latest |
| `8.5` | Latest |

`fail-fast: false` is set so all versions run to completion independently — a failure on one PHP
version does not cancel the others.

---

## Local usage

```zsh
# Install dev dependencies (requires PHP 8.1+)
composer install

# Check for errors
composer cs

# Auto-fix what can be fixed, then recheck
composer cs:fixable && composer cs
```
