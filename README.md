<p align="center">
    <img src="readme-lint-logo.png"
         alt="Readme-lint logo"
         title="Readme-lint logo">
</p>

![Test Status](https://github.com/raphaelstolt/readme-lint/workflows/test/badge.svg)
[![Version](http://img.shields.io/packagist/v/stolt/readme-lint.svg?style=flat)](https://packagist.org/packages/stolt/readme-lint)
![PHP Version](https://img.shields.io/badge/php-8.1+-ff69b4.svg)
![Downloads](https://img.shields.io/packagist/dt/stolt/readme-lint)
[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat)](https://github.com/php-pds/skeleton)

# readme-lint

This library and its CLI supports you in linting `README.md` Markdown files via PHP. For inspiration for good READMEs,
please have a look at the [Awesome README](https://github.com/matiassingers/awesome-readme) repository.

## Installation and usage

```bash
composer require --dev stolt/readme-lint
```

### Available CLI commands
The following list shows the currently two available CLI commands to interact with a README.md file.

``` bash
php bin/readme-lint list

readme-lint 0.0.2

Available commands:
  lint        Lint a README.md file for common quality issues
  spellcheck  Check a README.md file for wording or spelling mistakes
```

Available lint rules can be found in the [Rules](src/Rules) directory.

### Configuration

To configure the lint rules to apply, you have several options:

1. Create a `.readme-lint.php` file in the root of your project, which will be used as a default configuration when 
present.

2. Create a `some-readme-lint-configuration-file.php` file which you pass to the CLI via an option.

``` bash
php bin/readme-lint lint --config=some-readme-lint-configuration-file.php
```

3. Pass a list of rules as a comma-separated string to the CLI. You can use FQCNs or base names for the rules to apply.

``` bash
php bin/readme-lint lint --rules Stolt\ReadmeLint\Rules\LogoPresenceRule,NoTodoCommentRule
```

> [!NOTE]
> To add `custom lint rules`, you must first create a class which implements the `Stolt\ReadmeLint\Rule\RuleInterface` interface
> and then make them available via your `readme-lint` configuration file like shown next.

``` php
<?php declare(strict_types=1);

use Stolt\ReadmeLint\Configuration;
use Stolt\ReadmeLint\Linter;

$configuration = new Configuration(new Linter(getcwd()));
$configuration->setCustomRulesDirectory(
    '/some/path/to/custom/rules',
    'Custom\Rules\Namespace'
)->addRulesToApply(['CustomRuleA', 'CustomRuleB', 'NoTodoCommentRule']);

return $configuration;
```

### Running tests

``` bash
composer test
```

### License

This library and its CLI are licensed under the MIT license. Please see [LICENSE.md](LICENSE.md) for more details.

### Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more details.

### Contributing

Please see [CONTRIBUTING.md](.github/CONTRIBUTING.md) for more details.
