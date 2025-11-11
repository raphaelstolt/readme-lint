<?php declare(strict_types=1);

namespace Stolt\ReadmeLint\Commands\Repository;

class TemplateRepository
{
    public function getDefaultReadmeTemplate(string $projectName): string
    {
        return <<<MD
# {$projectName}

Short project description.

## Installation and usage

```bash
composer require {$projectName}
```

Longer usage instructions.

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

MD;
    }
}
