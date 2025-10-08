<p align="center">
    <img src="readme-lint-logo.png"
         alt="Readme-lint logo"
         title="Readme-lint logo">
</p>

# readme-lint

This library and its CLI supports you in linting `README.md` Markdown files via PHP.

## Installation and usage

```bash
composer require --dev stolt/readme-lint
```

### Available CLI commands
The following list shows the currently available CLI commands to interact with a README.md file.

``` bash
php bin/readme-lint list

readme-lint 0.0.1

Available commands:
  lint        Lint a README.md file for common quality issues
```

Usable rules can be found in the [Rules](src/Rules) directory.

### Running tests

``` bash
composer test
```

### License

This library is licensed under the MIT license. Please see [LICENSE.md](LICENSE.md) for more details.

### Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more details.

### Contributing

Please see [CONTRIBUTING.md](.github/CONTRIBUTING.md) for more details.
