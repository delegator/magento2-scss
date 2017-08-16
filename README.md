# delegator/magento2-scss

This Magento 2 module allows the use of SCSS for your CSS preprocessor, instead
of Magento's default choice of LESS. It uses the [scssphp][scssphp] compiler to
compile your SCSS.

## Installation

This module is intended to be installed using composer.

1. Add the Delegator composer repository to your `composer.json` by following
the documentation on the repository page: https://packages.delegator.com/

2. Add the `delegator/magento2-scss` module to your project:
```bash
$ composer require delegator/magento2-scss
```

3. Create a `.scss` file in your theme and [pull it in via layout XML][layout-xml].

4. This module will automatically process your SCSS in the same way Magento 2
processes its own LESS files.

## Tests

Unit tests can be found in the [Test/Unit](Test/Unit) directory.

## Contributors

Magento Core team

## License

[Open Source License](LICENSE.txt)

[scssphp]: https://leafo.github.io/scssphp/
[layout-xml]: http://devdocs.magento.com/guides/v2.1/frontend-dev-guide/layouts/xml-manage.html
