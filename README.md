# The Contao Maker Bundle

[![](https://img.shields.io/packagist/v/contao/maker-bundle.svg?style=flat-square)](https://packagist.org/packages/contao/maker-bundle)
[![](https://img.shields.io/packagist/dt/contao/maker-bundle.svg?style=flat-square)](https://packagist.org/packages/contao/maker-bundle)

The Contao Maker bundle allows you to generate Content Elements, Frontend Modules and
Hooks using interactive commands.

## Installation

Run this command to install and enable this bundle in your application:

```
composer require contao/maker-bundle --dev
```

## Usage

This bundle provides several commands under the make: namespace.
List them all executing this command:

```
⇢ symfony php bin/console list make:contao
  [...]

  make:contao:content-element  Creates an empty content element
  make:contao:frontend-module  Creates an empty frontend module
  make:contao:hook             Creates a hook
```

## License

Contao is licensed under the terms of the LGPLv3.

## Getting support

Visit the [support page][2] to learn about the available support options.

[1]: https://contao.org
[2]: https://contao.org/en/support.html
